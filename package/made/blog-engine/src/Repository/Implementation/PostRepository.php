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

namespace Made\Blog\Engine\Repository\Implementation;

use DateTime;
use Made\Blog\Engine\Exception\UnsupportedOperationException;
use Made\Blog\Engine\Model\Post;
use Made\Blog\Engine\Model\PostConfiguration;
use Made\Blog\Engine\Model\PostConfigurationLocale;
use Made\Blog\Engine\Repository\Criteria\CriteriaLocale;
use Made\Blog\Engine\Repository\PostConfigurationLocaleRepositoryInterface;
use Made\Blog\Engine\Repository\PostConfigurationRepositoryInterface;
use Made\Blog\Engine\Repository\PostRepositoryInterface;
use Made\Blog\Engine\Service\PostContentResolverInterface;

/**
 * Class PostRepository
 *
 * @package Made\Blog\Engine\Repository\Implementation
 */
class PostRepository implements PostRepositoryInterface
{
    /**
     * @var PostConfigurationRepositoryInterface
     */
    private $postConfigurationRepository;

    /**
     * @var PostConfigurationLocaleRepositoryInterface
     */
    private $postConfigurationLocaleRepository;

    /**
     * @var PostContentResolverInterface
     */
    private $postContentResolver;

    /**
     * PostRepository constructor.
     * @param PostConfigurationRepositoryInterface $postConfigurationRepository
     * @param PostConfigurationLocaleRepositoryInterface $postConfigurationLocaleRepository
     * @param PostContentResolverInterface $postContentResolver
     */
    public function __construct(PostConfigurationRepositoryInterface $postConfigurationRepository, PostConfigurationLocaleRepositoryInterface $postConfigurationLocaleRepository, PostContentResolverInterface $postContentResolver)
    {
        $this->postConfigurationRepository = $postConfigurationRepository;
        $this->postConfigurationLocaleRepository = $postConfigurationLocaleRepository;
        $this->postContentResolver = $postContentResolver;
    }

    /**
     * @inheritDoc
     * @throws UnsupportedOperationException
     */
    public function create(Post $post): bool
    {
        throw new UnsupportedOperationException('Unsupported operation: ' . __METHOD__ . '! '
            . 'The post repository can not be used for that type of action.');
    }

    /**
     * @inheritDoc
     */
    public function getAll(CriteriaLocale $criteria): array
    {
        $all = $this->postConfigurationLocaleRepository
            ->getAll($criteria);

        return $this->convertToPost($all);
    }

    /**
     * @inheritDoc
     */
    public function getAllByPostDate(CriteriaLocale $criteria, DateTime $dateTime): array
    {
        $all = $this->postConfigurationLocaleRepository
            ->getAllByPostDate($criteria, $dateTime);

        return $this->convertToPost($all);
    }

    /**
     * @inheritDoc
     */
    public function getAllByStatus(CriteriaLocale $criteria, string ...$statusList): array
    {
        $all = $this->postConfigurationLocaleRepository
            ->getAllByStatus($criteria, ...$statusList);

        return $this->convertToPost($all);
    }

    /**
     * @inheritDoc
     */
    public function getAllByCategory(CriteriaLocale $criteria, string ...$categoryList): array
    {
        $all = $this->postConfigurationLocaleRepository
            ->getAllByCategory($criteria, ...$categoryList);

        return $this->convertToPost($all);
    }

    /**
     * @inheritDoc
     */
    public function getAllByTag(CriteriaLocale $criteria, string ...$tagList): array
    {
        $all = $this->postConfigurationLocaleRepository
            ->getAllByTag($criteria, ...$tagList);

        return $this->convertToPost($all);
    }

    /**
     * @inheritDoc
     */
    public function getOneById(string $locale, string $id): ?Post
    {
        $one = $this->postConfigurationLocaleRepository
            ->getOneById($locale, $id);

        $allPost = $this->convertToPost([
            $one,
        ]);

        return reset($allPost) ?: null;
    }

    /**
     * @inheritDoc
     */
    public function getOneBySlug(string $locale, string $slug): ?Post
    {
        $one = $this->postConfigurationLocaleRepository
            ->getOneBySlug($locale, $slug);

        $allPost = $this->convertToPost([
            $one,
        ]);

        return reset($allPost) ?: null;
    }

    /**
     * @inheritDoc
     */
    public function getOneBySlugRedirect(string $locale, string $slugRedirect): ?Post
    {
        $one = $this->postConfigurationLocaleRepository
            ->getOneBySlugRedirect($locale, $slugRedirect);

        $allPost = $this->convertToPost([
            $one,
        ]);

        return reset($allPost) ?: null;
    }

    /**
     * @inheritDoc
     * @throws UnsupportedOperationException
     */
    public function modify(Post $post): bool
    {
        throw new UnsupportedOperationException('Unsupported operation: ' . __METHOD__ . '! '
            . 'The post repository can not be used for that type of action.');
    }

    /**
     * @inheritDoc
     * @throws UnsupportedOperationException
     */
    public function destroy(Post $post): bool
    {
        throw new UnsupportedOperationException('Unsupported operation: ' . __METHOD__ . '! '
            . 'The post repository can not be used for that type of action.');
    }

    /**
     * @param array|PostConfigurationLocale[] $all
     * @return array|Post[]
     */
    private function convertToPost(array $all): array
    {
        $allPost = array_map(function (?PostConfigurationLocale $postConfigurationLocale): ?Post {
            if (null !== $postConfigurationLocale) {
                $postConfiguration = $this->postConfigurationRepository
                    ->getOneById($postConfigurationLocale->getId());
                $postConfiguration->setLocale($postConfigurationLocale, PostConfiguration::LOCALE_KEY_CURRENT);
                $postContent = $this->postContentResolver
                    ->resolve($postConfigurationLocale);

                // Make sure there are only posts with content.
                if (null !== $postContent) {
                    return (new Post())
                        ->setConfiguration($postConfiguration)
                        ->setContent($postContent);
                }
            }

            return null;
        }, $all);

        return array_filter($allPost, function (?Post $post): bool {
            return null !== $post;
        });
    }
}
