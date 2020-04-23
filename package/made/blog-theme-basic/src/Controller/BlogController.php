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

namespace Made\Blog\Theme\Basic\Controller;

use DateTime;
use Help\Path;
use Help\Slug;
use Made\Blog\Engine\Exception\FailedOperationException;
use Made\Blog\Engine\Model\Configuration;
use Made\Blog\Engine\Model\Post;
use Made\Blog\Engine\Model\PostConfiguration;
use Made\Blog\Engine\Model\PostConfigurationLocale;
use Made\Blog\Engine\Repository\AuthorRepositoryInterface;
use Made\Blog\Engine\Repository\CategoryRepositoryInterface;
use Made\Blog\Engine\Repository\Criteria\Criteria;
use Made\Blog\Engine\Repository\Criteria\CriteriaLocale;
use Made\Blog\Engine\Repository\Criteria\Factory\FilterFactory;
use Made\Blog\Engine\Repository\Criteria\Factory\OrderFactory;
use Made\Blog\Engine\Repository\PostConfigurationLocaleRepositoryInterface;
use Made\Blog\Engine\Repository\PostRepositoryInterface;
use Made\Blog\Engine\Repository\TagRepositoryInterface;

/**
 * Class BlogController
 *
 * A framework independent controller. Based on the following blog article series:
 *
 * - {@link https://matthiasnoback.nl/2014/06/how-to-create-framework-independent-controllers/}
 * - {@link https://matthiasnoback.nl/2014/06/don-t-use-annotations-in-your-controllers/}
 * - {@link https://matthiasnoback.nl/2014/06/framework-independent-controllers-part-3/}
 *
 * @package Made\Blog\Theme\Basic\Controller
 */
class BlogController
{
    const VARIABLE_REDIRECT = 'redirect';

    const VARIABLE_LOCALE = 'locale';
    const VARIABLE_TEMPLATE = 'template';
    const VARIABLE_DATA = 'data';

    const VARIABLE_DATA_LOCALE = 'locale';
    const VARIABLE_DATA_POST = 'post';
    const VARIABLE_DATA_POST_LIST = 'postList';
    const VARIABLE_DATA_POST_LIST_DATE = 'postListDate';
    const VARIABLE_DATA_CATEGORY_LIST = 'categoryList';
    const VARIABLE_DATA_CATEGORY_LIST_ALL = 'categoryListAll';
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

    const PAGE_SIZE_POST_LIST = 10;
    const PAGE_SIZE_POST_LIST_ALL = 3;
    const PAGE_SIZE_CATEGORY_LIST = 10;
    const PAGE_SIZE_TAG_LIST = 10;
    const PAGE_SIZE_AUTHOR_LIST = 10;

    /**
     * @var array
     */
    protected $pageData;

    /**
     * @var Configuration
     */
    protected $configuration;

    /**
     * @var CategoryRepositoryInterface
     */
    protected $categoryRepository;

    /**
     * @var TagRepositoryInterface
     */
    protected $tagRepository;

    /**
     * @var AuthorRepositoryInterface
     */
    protected $authorRepository;

    /**
     * @var PostConfigurationLocaleRepositoryInterface
     */
    protected $postConfigurationLocaleRepository;

    /**
     * @var PostRepositoryInterface
     */
    protected $postRepository;

    /**
     * BlogController constructor.
     * @param array $pageData
     * @param Configuration $configuration
     * @param CategoryRepositoryInterface $categoryRepository
     * @param TagRepositoryInterface $tagRepository
     * @param AuthorRepositoryInterface $authorRepository
     * @param PostConfigurationLocaleRepositoryInterface $postConfigurationLocaleRepository
     * @param PostRepositoryInterface $postRepository
     */
    public function __construct(array $pageData, Configuration $configuration, CategoryRepositoryInterface $categoryRepository, TagRepositoryInterface $tagRepository, AuthorRepositoryInterface $authorRepository, PostConfigurationLocaleRepositoryInterface $postConfigurationLocaleRepository, PostRepositoryInterface $postRepository)
    {
        $this->pageData = $pageData;
        $this->configuration = $configuration;
        $this->categoryRepository = $categoryRepository;
        $this->tagRepository = $tagRepository;
        $this->authorRepository = $authorRepository;
        $this->postConfigurationLocaleRepository = $postConfigurationLocaleRepository;
        $this->postRepository = $postRepository;
    }

    /**
     * @return array|null
     */
    public function rootAction(): ?array
    {
        $locale = $this->configuration
            ->getFallbackLocale();

        $slug = Path::join(...[
            $locale,
        ]);
        $slug = Slug::sanitize($locale);

        return $this->createRedirect($slug);
    }

    /**
     * @param string $locale
     * @return array|null
     * @throws FailedOperationException
     */
    public function homeAction(string $locale): ?array
    {
        $tagListCriteria = (new Criteria())
            ->setOrder(OrderFactory::byName_Tag());
        $tagList = $this->tagRepository
            ->getAll($tagListCriteria);

        // TODO: Get the configured promoted posts instead of the most recent ones.
        $postListCriteria = (new CriteriaLocale($locale))
            ->setLimit(static::PAGE_SIZE_POST_LIST_ALL)
            ->setOrder(OrderFactory::byDate_PostConfigurationLocale());

        $postList = $this->postRepository
            ->getAll($postListCriteria);

        $template = $this->getTemplate(static::TEMPLATE_NAME_HOME);

        return $this->createData($locale, $template, [
            static::VARIABLE_DATA_TAG_LIST => $tagList, // TODO
            static::VARIABLE_DATA_POST_LIST => $postList,
        ]);
    }

    /**
     * @param string $locale
     * @param int $page
     * @return array|null
     * @throws FailedOperationException
     */
    public function postListAction(string $locale, int $page = 0): ?array
    {
        /** @var CriteriaLocale $postListCriteria */
        $postListCriteria = (new CriteriaLocale($locale))
            ->setOrder(OrderFactory::byDate_PostConfigurationLocale());
        $postListCriteria = $this->setPage($postListCriteria, $page, static::PAGE_SIZE_POST_LIST);

        $postList = $this->postRepository
            ->getAll($postListCriteria);

        $template = $this->getTemplate(static::TEMPLATE_NAME_POST_OVERVIEW);

        return $this->createData($locale, $template, [
            static::VARIABLE_DATA_POST_LIST => $postList,
        ]);
    }

    /**
     * @param string $locale
     * @param DateTime $dateTime
     * @return array|null
     * @throws FailedOperationException
     */
    public function postListDateAction(string $locale, DateTime $dateTime): ?array
    {
        /** @var CriteriaLocale $postListCriteria */
        $postListCriteria = (new CriteriaLocale($locale))
            ->setFilter(
                FilterFactory::byDate($dateTime, PostConfigurationLocale::class, 'Y-m', null)
            )
            ->setOrder(
                OrderFactory::byDate_PostConfigurationLocale()
            );

        $postList = $this->postRepository
            ->getAll($postListCriteria);

        $template = $this->getTemplate(static::TEMPLATE_NAME_POST_OVERVIEW);

        return $this->createData($locale, $template, [
            static::VARIABLE_DATA_POST_LIST => $postList,
        ]);
    }

    /**
     * @param string $locale
     * @param string $slug
     * @return array|null
     * @throws FailedOperationException
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
     * @param int $page
     * @return array|null
     * @throws FailedOperationException
     */
    public function categoryListAction(string $locale, int $page = 0): ?array
    {
        /** @var Criteria $categoryListCriteria */
        $categoryListCriteria = (new Criteria())
            ->setOrder(OrderFactory::byName_Category());
        $categoryListCriteria = $this->setPage($categoryListCriteria, $page, static::PAGE_SIZE_CATEGORY_LIST);

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
     * @throws FailedOperationException
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
     * @param int $page
     * @return array|null
     * @throws FailedOperationException
     */
    public function tagListAction(string $locale, int $page = 0): ?array
    {
        /** @var Criteria $tagListCriteria */
        $tagListCriteria = (new Criteria())
            ->setOrder(OrderFactory::byName_Tag());
        $tagListCriteria = $this->setPage($tagListCriteria, $page, static::PAGE_SIZE_TAG_LIST);

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
     * @throws FailedOperationException
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
     * @param int $page
     * @return array|null
     * @throws FailedOperationException
     */
    public function authorListAction(string $locale, int $page = 0): ?array
    {
        /** @var Criteria $authorListCriteria */
        $authorListCriteria = (new Criteria())
            ->setOrder(OrderFactory::byName_Author());
        $authorListCriteria = $this->setPage($authorListCriteria, $page, static::PAGE_SIZE_AUTHOR_LIST);

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
     * @throws FailedOperationException
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
     * @param int $page
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
     * @throws FailedOperationException
     */
    protected function createData(string $locale, string $template, array $data): ?array
    {
        $categoryListAllCriteria = (new Criteria())
            ->setOrder(OrderFactory::byName_Category());
        $categoryListAll = $this->categoryRepository
            ->getAll($categoryListAllCriteria);

        $postConfigurationLocaleListCriteria = (new CriteriaLocale($locale))
            ->setOrder(OrderFactory::byDate_PostConfigurationLocale());
        $postConfigurationLocaleList = $this->postConfigurationLocaleRepository
            ->getAll($postConfigurationLocaleListCriteria);

        $postListDate = array_map(function (PostConfigurationLocale $postConfigurationLocale): DateTime {
            return $postConfigurationLocale->getDate();
        }, $postConfigurationLocaleList);
        $postListDate = array_reduce($postListDate, function (array $carry, DateTime $dateTime): array {
            $key = $dateTime->format('Y-m');

            if (!array_key_exists($key, $carry)) {
                $carry[$key] = $dateTime;
            }

            return $carry;
        }, []);
        $postListDate = array_values($postListDate);

        // Data has to be merged/replaced in the defaults, so it can be overridden. But that has to be non-recursive.
        // Also put the page data (settings) before that. That ways reserved keys are not taken by configuration.
        $data = array_replace($this->pageData, [
            static::VARIABLE_DATA_LOCALE => $locale,
            static::VARIABLE_DATA_CATEGORY_LIST_ALL => $categoryListAll,
            static::VARIABLE_DATA_POST_LIST_DATE => $postListDate,
        ], $data);

        return [
            static::VARIABLE_LOCALE => $locale,
            static::VARIABLE_TEMPLATE => $template,
            static::VARIABLE_DATA => $data,
        ];
    }

    /**
     * @param Criteria $criteria
     * @param int $page
     * @param int $pageSize
     * @return Criteria
     */
    protected function setPage(Criteria $criteria, int $page, int $pageSize): Criteria
    {
        if ($page < 0) {
            return $criteria;
        }

        if ($pageSize < 1) {
            return $criteria;
        }

        return $criteria
            ->setOffset($page * $pageSize)
            ->setLimit($pageSize);
    }
}
