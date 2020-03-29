<?php
/**
 * Made Blog
 * Copyright (c) 2019-2020 Made
 *
 * This program  is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 */

namespace Made\Blog\Engine\Service\PageDataProvider\Implementation\Base;

use Help\Path;
use Help\Slug;
use Made\Blog\Engine\Exception\InvalidArgumentException;
use Made\Blog\Engine\Exception\UnsupportedOperationException;
use Made\Blog\Engine\Model\Configuration;
use Made\Blog\Engine\Model\Post;
use Made\Blog\Engine\Model\PostConfiguration;
use Made\Blog\Engine\Repository\CategoryRepositoryInterface;
use Made\Blog\Engine\Repository\Criteria\Criteria;
use Made\Blog\Engine\Repository\PostRepositoryInterface;
use Made\Blog\Engine\Repository\TagRepositoryInterface;
use Made\Blog\Engine\Service\PageDataProviderInterface;

/**
 * Class PageDataProvider
 *
 * @package Made\Blog\Engine\Service\PageDataProvider\Implementation\Base
 */
class PageDataProvider implements PageDataProviderInterface
{
    const MATCH_FULL = 'full';
    const MATCH_LOCALE = 'locale';
    const MATCH_SLUG = 'slug';

    const MATCH_ID_CATEGORY = 'category';
    const MATCH_ID_TAG = 'category';

    /**
     * TODO: Add regex101.com link.
     */
    const PATTERN_CATEGORY = '/^\/category\/([\w\-]+)\/?$/';

    /**
     * TODO: Add regex101.com link.
     */
    const PATTERN_TAG = '/^\/tag\/([\w\-]+)\/?$/';

    const VARIABLE_TEMPLATE = 'template';
    const VARIABLE_REDIRECT = 'redirect';

    const VARIABLE_CATEGORY_LIST = 'categoryList';
    const VARIABLE_CATEGORY = 'category';

    const VARIABLE_TAG_LIST = 'tagList';
    const VARIABLE_TAG = 'tag';

    const VARIABLE_POST = 'post';

    // Template paths below:

    const TEMPLATE_HOME = '@theme-base/home.html.twig';

    const TEMPLATE_CATEGORY_OVERVIEW = '@theme-base/category-overview.html.twig';
    const TEMPLATE_CATEGORY = '@theme-base/category.html.twig';

    const TEMPLATE_TAG_OVERVIEW = '@theme-base/tag-overview.html.twig';
    const TEMPLATE_TAG = '@theme-base/tag.html.twig';

    /**
     * @var array
     */
    private $pageData;

    /**
     * @var Configuration
     */
    private $configuration;

    /**
     * @var CategoryRepositoryInterface
     */
    private $categoryRepository;

    /**
     * @var TagRepositoryInterface
     */
    private $tagRepository;

    /**
     * @var PostRepositoryInterface
     */
    private $postRepository;

    /**
     * PageDataProvider constructor.
     * @param array $pageData
     * @param Configuration $configuration
     * @param CategoryRepositoryInterface $categoryRepository
     * @param TagRepositoryInterface $tagRepository
     * @param PostRepositoryInterface $postRepository
     */
    public function __construct(array $pageData, Configuration $configuration, CategoryRepositoryInterface $categoryRepository, TagRepositoryInterface $tagRepository, PostRepositoryInterface $postRepository)
    {
        $this->pageData = $pageData;
        $this->configuration = $configuration;
        $this->categoryRepository = $categoryRepository;
        $this->tagRepository = $tagRepository;
        $this->postRepository = $postRepository;
    }

    /**
     * @inheritDoc
     */
    public function accept(array $slugData): bool
    {
        [
            static::MATCH_FULL => $matchFull,
        ] = $slugData;

        // At least it has to match.
        return (null !== $matchFull);
    }

    /**
     * @inheritDoc
     * @throws InvalidArgumentException
     * @throws UnsupportedOperationException
     */
    public function provide(array $slugData): ?array
    {
        [
            /** @var string|null $matchLocale */
            static::MATCH_LOCALE => $matchLocale,
            /** @var string|null $matchSlug */
            static::MATCH_SLUG => $matchSlug,
        ] = $slugData;

        // If no slug was matched, we are probably at one of these slugs:
        // - /
        // - /de
        // - /en
        if (null === $matchSlug) {

            // If no locale was matched, we are probably at one of these slugs:
            // - /
            if (null === $matchLocale) {
                return $this->provideDataRoot();
            }

            return $this->provideDataHome($matchLocale);
        } else {
            if (null === $matchLocale) {
                throw new InvalidArgumentException('Malformed slug data.');
            }

            // Else we are probably at one of these slugs:
            // - /de/category
            // - /en/category
            // - /de/category/10
            // - /en/category/10
            // - /de/tag
            // - /en/tag
            // - /de/tag/10
            // - /en/tag/10
            // - /de/post-slug
            // - /en/post-slug
            // So further matching is needed.

            $matchSlug = Slug::sanitize($matchSlug);

            // If the slug starts with '/category', we are probably at one of these slugs:
            // - /de/category
            // - /en/category
            // - /de/category/10
            // - /en/category/10
            // So further matching is needed.
            if (0 === strpos($matchSlug, '/category')) {
                return $this->provideCategory($matchLocale, $matchSlug);
            }

            // If the slug starts with '/tag', we are probably at one of these slugs:
            // - /de/tag
            // - /en/tag
            // - /de/tag/10
            // - /en/tag/10
            if (0 === strpos($matchSlug, '/tag')) {
                return $this->provideTag($matchLocale, $matchSlug);
            }

            // Else we are probably at one of these slugs:
            // - /de/post-slug
            // - /en/post-slug

            return $this->provideDataPost($matchLocale, $matchSlug);
        }
    }

    /**
     * @return array|null
     */
    protected function provideDataRoot(): ?array
    {
        $locale = $this->configuration
            ->getFallbackLocale();
        $slug = Slug::sanitize($locale);

        return [
            static::VARIABLE_REDIRECT => $slug,
        ];
    }

    /**
     * @param $locale
     * @return array|null
     * @throws UnsupportedOperationException
     */
    protected function provideDataHome(string $locale): ?array
    {
        throw new UnsupportedOperationException('Currently not implemented! (' . __METHOD__ . ')');
    }

    /**
     * @param string $locale
     * @param string $slug
     * @return array|null
     */
    protected function provideCategory(string $locale, string $slug): ?array
    {
        // If the slug is '/category' exactly, we are probably at one of these slugs:
        // - /de/category
        // - /en/category
        if ('/category' === $slug) {
            return $this->provideDataCategoryOverview($locale);
        }

        $identifier = [
            static::MATCH_FULL,
            static::MATCH_ID_CATEGORY,
        ];
        $match = [];

        $result = preg_match(static::PATTERN_CATEGORY, $slug, $match, PREG_UNMATCHED_AS_NULL);
        if (0 === $result || false === $result) {
            $match = array_fill(0, count($identifier), null);
        }

        $match = array_combine($identifier, $match) ?: [];
        /** @var string $matchId */
        $matchId = $match[static::MATCH_ID_CATEGORY];

        return $this->provideDataCategory($locale, $matchId);
    }

    /**
     * TODO: Implement filtering and pagination.
     *
     * @param string $locale
     * @return array|null
     */
    protected function provideDataCategoryOverview(string $locale): ?array
    {
        $categoryListCriteria = new Criteria();
        $categoryList = $this->categoryRepository
            ->getAll($categoryListCriteria);

        return $this->provideData([
            static::VARIABLE_TEMPLATE => static::TEMPLATE_CATEGORY_OVERVIEW,
            static::VARIABLE_CATEGORY_LIST => $categoryList,
        ]);
    }

    /**
     * @param string $locale
     * @param string $id
     * @return array|null
     */
    protected function provideDataCategory(string $locale, string $id): ?array
    {
        $category = $this->categoryRepository
            ->getOneById($id);

        if (null === $category) {
            return null;
        }

        return $this->provideData([
            static::VARIABLE_TEMPLATE => static::TEMPLATE_CATEGORY,
            static::VARIABLE_CATEGORY => $category,
        ]);
    }

    /**
     * @param string $locale
     * @param string $slug
     * @return array|null
     */
    protected function provideTag(string $locale, string $slug): ?array
    {
        // If the slug is '/tag' exactly, we are probably at one of these slugs:
        // - /de/tag
        // - /en/tag
        if ('/tag' === $slug) {
            return $this->provideDataTagOverview($locale);
        }

        $identifier = [
            static::MATCH_FULL,
            static::MATCH_ID_TAG,
        ];
        $match = [];

        $result = preg_match(static::PATTERN_TAG, $slug, $match, PREG_UNMATCHED_AS_NULL);
        if (0 === $result || false === $result) {
            $match = array_fill(0, count($identifier), null);
        }

        $match = array_combine($identifier, $match) ?: [];
        /** @var string $matchId */
        $matchId = $match[static::MATCH_ID_TAG];

        return $this->provideDataTag($locale, $matchId);
    }

    /**
     * TODO: Implement filtering and pagination.
     *
     * @param string $locale
     * @return array|null
     */
    protected function provideDataTagOverview(string $locale): ?array
    {
        $tagListCriteria = new Criteria();
        $tagList = $this->tagRepository
            ->getAll($tagListCriteria);

        return $this->provideData([
            static::VARIABLE_TEMPLATE => static::TEMPLATE_TAG_OVERVIEW,
            static::VARIABLE_TAG_LIST => $tagList,
        ]);
    }

    /**
     * @param string $locale
     * @param string $id
     * @return array|null
     */
    protected function provideDataTag(string $locale, string $id): ?array
    {
        $tag = $this->tagRepository
            ->getOneById($id);

        if (null === $tag) {
            return null;
        }

        return $this->provideData([
            static::VARIABLE_TEMPLATE => static::TEMPLATE_TAG,
            static::VARIABLE_TAG => $tag,
        ]);
    }

    /**
     * @param string $locale
     * @param string $slugRedirect
     * @return array|null
     */
    protected function provideDataPost(string $locale, string $slugRedirect): ?array
    {
        // Try to find the post.
        $post = $this->postRepository
            ->getOneBySlug($locale, $slugRedirect);

        // If it is not found, try to find it by slug-redirect.
        if (null === $post) {
            $post = $this->postRepository
                ->getOneBySlugRedirect($locale, $slugRedirect);

            // And if it has not been found by now, give up.
            if (null === $post) {
                return null;
            }

            $slugRedirect = $this->getSlugPost($post);

            return $this->provideData([
                static::VARIABLE_REDIRECT => $slugRedirect,
            ]);
        }

        // Else, just render the page as usual.
        $template = $this->getTemplatePost($post);

        return $this->provideData([
            // TODO: Add categoryList, tagList and more data as needed.
            static::VARIABLE_TEMPLATE => $template,
            static::VARIABLE_POST => $post,
        ]);
    }

    /**
     * @param array $pageDataExtra
     * @return array|null
     */
    protected function provideData(array $pageDataExtra): ?array
    {
        return array_replace_recursive($this->pageData, $pageDataExtra);
    }

    /**
     * @param Post $post
     * @return string
     */
    protected final function getTemplatePost(Post $post): string
    {
        $configuration = $post->getConfiguration()
            ->getLocale(PostConfiguration::LOCALE_KEY_CURRENT);

        return $configuration->getTemplate();
    }

    /**
     * @param Post $post
     * @return string
     */
    private function getSlugPost(Post $post): string
    {
        $configuration = $post->getConfiguration()
            ->getLocale(PostConfiguration::LOCALE_KEY_CURRENT);

        $locale = $configuration->getLocale();
        $slug = $configuration->getSlug();

        $slug = Path::join($locale, $slug);
        $slug = Slug::sanitize($slug);

        return $slug;
    }
}
