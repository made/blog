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

use Made\Blog\Engine\Model\Configuration;
use Made\Blog\Engine\Repository\CategoryRepositoryInterface;
use Made\Blog\Engine\Repository\PostRepositoryInterface;
use Made\Blog\Engine\Repository\TagRepositoryInterface;
use Made\Blog\Engine\Service\PageDataProviderInterface;
use Pimple\Container;
use Pimple\Package\Exception\PackageException;
use Pimple\Package\PackageAbstract;
use Pimple\Package\PackageInterface;

/**
 * Class Package
 *
 * @package Made\Blog\Engine\Service\PageDataProvider\Implementation\Base
 */
class Package extends PackageAbstract
{
    /**
     * @inheritDoc
     * @throws PackageException
     */
    public function register(Container $pimple): void
    {
        parent::register($pimple);

        if (!$this->hasTagSupport(false)) {
            $this->addTagSupport();
        }

        if (!$this->hasConfigurationSupport(false)) {
            $this->addConfigurationSupport();
        }

        if (!$this->checkPackagePrerequisite(true)) {
            return;
        }

        $this->registerPageDataProvider();
    }

    /**
     * @throws PackageException
     */
    private function registerPageDataProvider(): void
    {
        $this->registerConfiguration(PageDataProvider::class, [
        ]);

        $configuration = $this->container[static::SERVICE_NAME_CONFIGURATION];

        $this->registerTagAndService(PageDataProviderInterface::TAG_PAGE_DATA_PROVIDER, PageDataProvider::class, function (Container $container) use ($configuration): PageDataProviderInterface {
            $settings = $configuration[PageDataProvider::class];

            unset($configuration);

            /** @var Configuration $configuration */
            $configuration = $container[Configuration::class];
            /** @var CategoryRepositoryInterface $categoryRepository */
            $categoryRepository = $container[CategoryRepositoryInterface::class];
            /** @var TagRepositoryInterface $tagRepository */
            $tagRepository = $container[TagRepositoryInterface::class];
            /** @var PostRepositoryInterface $postRepository */
            $postRepository = $container[PostRepositoryInterface::class];

            return new PageDataProvider($settings, $configuration, $categoryRepository, $tagRepository, $postRepository);
        });
    }

    /**
     * @param bool $shouldThrow
     * @return bool
     * @throws PackageException
     */
    private function checkPackagePrerequisite(bool $shouldThrow = false): bool
    {
        $packagePrerequisite = (isset($this->container[Configuration::class]) && ($this->container[Configuration::class] instanceof Configuration));

        if (!$packagePrerequisite && $shouldThrow) {
            throw new PackageException('Container does not have package prerequsite!');
        }

        return $packagePrerequisite;
    }
}
