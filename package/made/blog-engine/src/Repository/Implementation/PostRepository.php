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
use Made\Blog\Engine\Model\Post;
use Made\Blog\Engine\Model\PostConfigurationLocale;
use Made\Blog\Engine\Repository\PostConfigurationLocaleRepositoryInterface;
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
     * @var PostConfigurationLocaleRepositoryInterface
     */
    private $postConfigurationLocaleRepository;

    /**
     * @var PostContentResolverInterface
     */
    private $postContentResolver;

    /**
     * PostRepository constructor.
     * @param PostConfigurationLocaleRepositoryInterface $postConfigurationLocaleRepository
     * @param PostContentResolverInterface $postContentResolver
     */
    public function __construct(PostConfigurationLocaleRepositoryInterface $postConfigurationLocaleRepository, PostContentResolverInterface $postContentResolver)
    {
        $this->postConfigurationLocaleRepository = $postConfigurationLocaleRepository;
        $this->postContentResolver = $postContentResolver;
    }

    /**
     * @inheritDoc
     */
    public function getAll(): array
    {
        $all = $this->postConfigurationLocaleRepository->getAll();

        return $this->convertToPost($all);
    }

    /**
     * @inheritDoc
     */
    public function getAllByPostDate(DateTime $dateTime): array
    {
        $all = $this->postConfigurationLocaleRepository->getAllByPostDate($dateTime);

        return $this->convertToPost($all);
    }

    /**
     * @inheritDoc
     */
    public function getAllByStatus(string ...$statusList): array
    {
        $all = $this->postConfigurationLocaleRepository->getAllByStatus(...$statusList);

        return $this->convertToPost($all);
    }

    /**
     * @inheritDoc
     */
    public function getAllByCategory(string ...$categoryList): array
    {
        $all = $this->postConfigurationLocaleRepository->getAllByCategory(...$categoryList);

        return $this->convertToPost($all);
    }

    /**
     * @inheritDoc
     */
    public function getAllByTag(string ...$tagList): array
    {
        $all = $this->postConfigurationLocaleRepository->getAllByTag(...$tagList);

        return $this->convertToPost($all);
    }

    /**
     * @inheritDoc
     */
    public function getOneById(string $id): ?Post
    {
        $one = $this->postConfigurationLocaleRepository->getOneById($id);

        $allPost = $this->convertToPost([
            $one,
        ]);

        return reset($allPost) ?: null;
    }

    /**
     * @inheritDoc
     */
    public function getOneBySlug(string $slug): ?Post
    {
        $one = $this->postConfigurationLocaleRepository->getOneBySlug($slug);

        $allPost = $this->convertToPost([
            $one,
        ]);

        return reset($allPost) ?: null;
    }

    /**
     * @inheritDoc
     */
    public function getOneBySlugRedirect(string $slugRedirect): ?Post
    {
        $one = $this->postConfigurationLocaleRepository->getOneBySlugRedirect($slugRedirect);

        $allPost = $this->convertToPost([
            $one,
        ]);

        return reset($allPost) ?: null;
    }

    /**
     * @param array|PostConfigurationLocale[] $all
     * @return array|Post[]
     */
    private function convertToPost(array $all): array
    {
        $allPost = array_map(function (?PostConfigurationLocale $postConfigurationLocale): ?Post {
            if (null !== $postConfigurationLocale) {
                $postContent = $this->postContentResolver->resolve($postConfigurationLocale);

                // Make sure there are only posts with content.
                if (null !== $postContent) {
                    return (new Post())
                        ->setConfiguration($postConfigurationLocale)
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
