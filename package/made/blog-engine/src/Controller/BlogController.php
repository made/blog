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

namespace Made\Blog\Engine\Controller;

use Help\Path;
use Help\Slug;
use Made\Blog\Engine\Model\Configuration;
use Made\Blog\Engine\Model\Post;
use Made\Blog\Engine\Model\PostConfiguration;
use Made\Blog\Engine\Repository\AuthorRepositoryInterface;
use Made\Blog\Engine\Repository\CategoryRepositoryInterface;
use Made\Blog\Engine\Repository\Criteria\Criteria;
use Made\Blog\Engine\Repository\Criteria\CriteriaLocale;
use Made\Blog\Engine\Repository\PostRepositoryInterface;
use Made\Blog\Engine\Repository\TagRepositoryInterface;

/**
 * Class BlogController
 *
 * A framework independent controller. Based on the following blog article series:
 *
 * - https://matthiasnoback.nl/2014/06/how-to-create-framework-independent-controllers/
 * - https://matthiasnoback.nl/2014/06/don-t-use-annotations-in-your-controllers/
 * - https://matthiasnoback.nl/2014/06/framework-independent-controllers-part-3/
 *
 * @package Made\Blog\Engine\Controller
 */
class BlogController
{
    const VARIABLE_REDIRECT = 'redirect';

    const VARIABLE_LOCALE = 'locale';
    const VARIABLE_TEMPLATE = 'template';
    const VARIABLE_DATA = 'data';

    const VARIABLE_DATA_POST = 'post';
    const VARIABLE_DATA_POST_LIST = 'postList';
    const VARIABLE_DATA_CATEGORY_LIST = 'categoryList';
    const VARIABLE_DATA_CATEGORY = 'category';
    const VARIABLE_DATA_TAG_LIST = 'tagList';
    const VARIABLE_DATA_TAG = 'tag';
    const VARIABLE_DATA_AUTHOR_LIST = 'authorList';
    const VARIABLE_DATA_AUTHOR = 'author';

    const TEMPLATE_NAME_HOME = 'home';
    const TEMPLATE_NAME_CATEGORY_OVERVIEW = 'category-overview';
    const TEMPLATE_NAME_CATEGORY = 'category';
    const TEMPLATE_NAME_TAG_OVERVIEW = 'tag-overview';
    const TEMPLATE_NAME_TAG = 'tag';
    const TEMPLATE_NAME_POST_OVERVIEW = 'post-overview';
    const TEMPLATE_NAME_AUTHOR = 'author';
    const TEMPLATE_NAME_AUTHOR_OVERVIEW = 'author-overview';

    const TEMPLATE_EXTENSION = '.html.twig';

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
     * @var AuthorRepositoryInterface
     */
    private $authorRepository;

    /**
     * @var PostRepositoryInterface
     */
    private $postRepository;

    /**
     * BlogController constructor.
     * @param array $pageData
     * @param Configuration $configuration
     * @param CategoryRepositoryInterface $categoryRepository
     * @param TagRepositoryInterface $tagRepository
     * @param AuthorRepositoryInterface $authorRepository
     * @param PostRepositoryInterface $postRepository
     */
    public function __construct(array $pageData, Configuration $configuration, CategoryRepositoryInterface $categoryRepository, TagRepositoryInterface $tagRepository, AuthorRepositoryInterface $authorRepository, PostRepositoryInterface $postRepository)
    {
        $this->pageData = $pageData;
        $this->configuration = $configuration;
        $this->categoryRepository = $categoryRepository;
        $this->tagRepository = $tagRepository;
        $this->authorRepository = $authorRepository;
        $this->postRepository = $postRepository;
    }

    /**
     * @return array|null
     */
    public function rootAction(): ?array
    {
        $locale = $this->configuration
            ->getFallbackLocale();
        $slug = Slug::sanitize($locale);

        return $this->createRedirect($slug);
    }

    /**
     * @param string $locale
     * @return array|null
     */
    public function homeAction(string $locale): ?array
    {
        $categoryListCriteria = new Criteria();
        $categoryList = $this->categoryRepository
            ->getAll($categoryListCriteria);

        $tagListCriteria = new Criteria();
        $tagList = $this->tagRepository
            ->getAll($tagListCriteria);

        $postListCriteria = new CriteriaLocale($locale);
        $postList = $this->postRepository
            ->getAll($postListCriteria);

        $template = $this->getTemplate(static::TEMPLATE_NAME_HOME);

        return $this->createData($locale, $template, [
            static::VARIABLE_DATA_CATEGORY_LIST => $categoryList,
            static::VARIABLE_DATA_TAG_LIST => $tagList,
            static::VARIABLE_DATA_POST_LIST => $postList,
        ]);
    }

    /**
     * @param string $locale
     * @param int $page TODO
     * @return array|null
     */
    public function postListAction(string $locale, int $page = 0): ?array
    {
        $categoryListCriteria = new Criteria();
        $categoryList = $this->categoryRepository
            ->getAll($categoryListCriteria);

        $template = $this->getTemplate(static::TEMPLATE_NAME_POST_OVERVIEW);

        return $this->createData($locale, $template, [
            static::VARIABLE_DATA_CATEGORY_LIST => $categoryList,
        ]);
    }

    /**
     * @param string $locale
     * @param string $slug
     * @return array|null
     */
    public function postAction(string $locale, string $slug): ?array
    {
        // Try to find the post.
        $post = $this->postRepository
            ->getOneBySlug($locale, $slug);

        // If it is not found, try to find it by slug-redirect.
        if (null === $post) {
            $post = $this->postRepository
                ->getOneBySlugRedirect($locale, $slug);

            // And if it has not been found by now, give up.
            if (null === $post) {
                return null;
            }

            $slugRedirect = $this->getSlugPost($post);

            return $this->createRedirect($slugRedirect);
        }

        // Else, just render the page as usual.
        $template = $this->getTemplatePost($post);

        return $this->createData($locale, $template, [
            static::VARIABLE_DATA_POST => $post,
        ]);
    }

    /**
     * @param string $locale
     * @param int $page TODO
     * @return array|null
     */
    public function categoryListAction(string $locale, int $page = 0): ?array
    {
        $categoryListCriteria = new Criteria();
        $categoryList = $this->categoryRepository
            ->getAll($categoryListCriteria);

        $template = $this->getTemplate(static::TEMPLATE_NAME_CATEGORY_OVERVIEW);

        return $this->createData($locale, $template, [
            static::VARIABLE_DATA_CATEGORY_LIST => $categoryList,
        ]);
    }

    /**
     * @param string $locale
     * @param string|null $id
     * @return array|null
     */
    public function categoryAction(string $locale, ?string $id = null): ?array
    {
        $category = null;

        if (null !== $id) {
            $category = $this->categoryRepository
                ->getOneById($id);
        }

        if (null === $category) {
            return null;
        }

        $template = $this->getTemplate(static::TEMPLATE_NAME_CATEGORY);

        return $this->createData($locale, $template, [
            static::VARIABLE_DATA_CATEGORY => $category,
        ]);
    }

    /**
     * @param string $locale
     * @param int $page TODO
     * @return array|null
     */
    public function tagListAction(string $locale, int $page = 0): ?array
    {
        $tagListCriteria = new Criteria();
        $tagList = $this->tagRepository
            ->getAll($tagListCriteria);

        $template = $this->getTemplate(static::TEMPLATE_NAME_TAG_OVERVIEW);

        return $this->createData($locale, $template, [
            static::VARIABLE_DATA_TAG_LIST => $tagList,
        ]);
    }

    /**
     * @param string $locale
     * @param string|null $id
     * @return array|null
     */
    public function tagAction(string $locale, ?string $id = null): ?array
    {
        $tag = null;

        if (null !== $id) {
            $tag = $this->tagRepository
                ->getOneById($id);
        }

        if (null === $tag) {
            return null;
        }

        $template = $this->getTemplate(static::TEMPLATE_NAME_TAG);

        return $this->createData($locale, $template, [
            static::VARIABLE_DATA_TAG => $tag,
        ]);
    }

    /**
     * @param string $locale
     * @param int $page TODO
     * @return array|null
     */
    public function authorListAction(string $locale, int $page = 0): ?array
    {
        $authorListCriteria = new Criteria();
        $authorList = $this->authorRepository
            ->getAll($authorListCriteria);

        $template = $this->getTemplate(static::TEMPLATE_NAME_AUTHOR_OVERVIEW);

        return $this->createData($locale, $template, [
            static::VARIABLE_DATA_AUTHOR_LIST => $authorList,
        ]);
    }

    /**
     * @param string $locale
     * @param string|null $name
     * @return array|null
     */
    public function authorAction(string $locale, ?string $name = null): ?array
    {
        $author = null;

        if (null !== $name) {
            $author = $this->authorRepository
                ->getOneByName($name);
        }

        if (null === $author) {
            return null;
        }

        $template = $this->getTemplate(static::TEMPLATE_NAME_AUTHOR);

        return $this->createData($locale, $template, [
            static::VARIABLE_DATA_AUTHOR => $author,
        ]);
    }

    /**
     * TODO
     *
     * @param string $locale
     * @param string|null $search
     * @param int $page TODO
     * @return array|null
     */
    public function searchAction(string $locale, ?string $search = null, int $page = 0): ?array
    {
        return null;
    }

    /**
     * @param string $template
     * @return string
     */
    protected function getTemplate(string $template): string
    {
        $template = trim($template);

        if (substr($template, -1 * strlen(self::TEMPLATE_EXTENSION)) !== self::TEMPLATE_EXTENSION) {
            $template .= self::TEMPLATE_EXTENSION;
        }

        if (0 !== strpos($template, '@')) {
            $themeName = $this->configuration
                ->getThemeName();

            $template = "@{$themeName}/{$template}";
        }

        return $template;
    }

    /**
     * @param Post $post
     * @return string
     */
    protected final function getTemplatePost(Post $post): string
    {
        $configuration = $post->getConfiguration()
            ->getLocale(PostConfiguration::LOCALE_KEY_CURRENT);
        $template = $configuration->getTemplate();

        return $this->getTemplate($template);
    }

    /**
     * @param Post $post
     * @return string
     */
    protected function getSlugPost(Post $post): string
    {
        $configuration = $post->getConfiguration()
            ->getLocale(PostConfiguration::LOCALE_KEY_CURRENT);

        $locale = $configuration->getLocale();
        $slug = $configuration->getSlug();

        $slug = Path::join($locale, $slug);
        $slug = Slug::sanitize($slug);

        return $slug;
    }

    /**
     * @param string $redirect
     * @return array|null
     */
    protected function createRedirect(string $redirect): ?array
    {
        return [
            static::VARIABLE_REDIRECT => $redirect,
        ];
    }

    /**
     * @param string $locale
     * @param string $template
     * @param array $data
     * @return array|null
     */
    private function createData(string $locale, string $template, array $data): ?array
    {
        $data = array_replace_recursive($data, [
            static::VARIABLE_LOCALE => $locale,
        ]);

        return [
            static::VARIABLE_LOCALE => $locale,
            static::VARIABLE_TEMPLATE => $template,
            static::VARIABLE_DATA => $data,
        ];
    }
}
