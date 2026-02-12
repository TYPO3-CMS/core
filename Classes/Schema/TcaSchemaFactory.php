<?php

declare(strict_types=1);

/*
 * This file is part of the TYPO3 CMS project.
 *
 * It is free software; you can redistribute it and/or modify it under
 * the terms of the GNU General Public License, either version 2
 * of the License, or any later version.
 *
 * For the full copyright and license information, please read the
 * LICENSE.txt file that was distributed with this source code.
 *
 * The TYPO3 project - inspiring people to share!
 */

namespace TYPO3\CMS\Core\Schema;

use Symfony\Component\DependencyInjection\Attribute\Autoconfigure;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use TYPO3\CMS\Core\Attribute\AsEventListener;
use TYPO3\CMS\Core\Cache\Event\CacheWarmupEvent;
use TYPO3\CMS\Core\Cache\Frontend\PhpFrontend;
use TYPO3\CMS\Core\Schema\Exception\UndefinedSchemaException;

/**
 * This factory returns an object representation of $GLOBALS['TCA']. It is injectable and built during bootstrap.
 *
 * A TcaSchema contains:
 * - a list of all fields as defined in [columns]
 * - a list of "capabilities" (parts defined in the [ctrl] section)
 * - a list of sub-schemata (if there is a [ctrl][type] definition, then sub-schemata are instances of TcaSchema itself again)
 * - a list of possible relations of other schemata pointing to this schema ("Passive Relations")
 */
#[Autoconfigure(public: true, shared: true)]
class TcaSchemaFactory
{
    protected SchemaCollection $schemata;

    public function __construct(
        protected readonly TcaSchemaBuilder $schemaBuilder,
        #[Autowire(expression: 'service("package-dependent-cache-identifier").withPrefix("TcaSchema").toString()')]
        protected readonly string $cacheIdentifier,
        #[Autowire(service: 'cache.core')]
        protected readonly PhpFrontend $cache,
    ) {
        $this->schemata = new SchemaCollection([]);
    }

    /**
     * Get a schema from the loaded TCA. Ensure to check for a schema with ->has() before
     * calling ->get().
     * @throws UndefinedSchemaException
     */
    public function get(string $schemaName): TcaSchema
    {
        return $this->schemata->get($schemaName);
    }

    /**
     * Checks if a schema exists, does not build the schema if not needed, thus it's very slim
     * and only creates a schema if a sub-schema is requested.
     */
    public function has(string $schemaName): bool
    {
        return $this->schemata->has($schemaName);
    }

    /**
     * Returns all main schemata
     *
     * @return SchemaCollection<string, TcaSchema>
     */
    public function all(): SchemaCollection
    {
        return $this->schemata;
    }

    /**
     * Only used for functional tests, which override TCA on the fly for specific test cases.
     * Modifying TCA other than in Configuration/TCA/Overrides must be avoided in production code.
     *
     * @internal only used for TYPO3 Core internally, never use it in public!
     */
    public function rebuild(array $fullTca): void
    {
        $this->schemata = $this->schemaBuilder->buildFromStructure($fullTca);
    }

    /**
     * Load TCA and populate all schema - throws away existing schema if $force is set.
     *
     * @internal only used for TYPO3 Core internally, never use it in public!
     */
    public function load(array $tca, bool $force = false): void
    {
        if (!$force && $this->schemata->count() > 0) {
            return;
        }
        if (!$force && $this->cache->has($this->cacheIdentifier)) {
            $this->schemata = $this->cache->require($this->cacheIdentifier);
            return;
        }
        $this->rebuild($tca);
        $this->cache->set($this->cacheIdentifier, 'return ' . var_export($this->schemata, true) . ';');
    }

    #[AsEventListener('typo3-core/tca-schema')]
    public function warmupCaches(CacheWarmupEvent $event): void
    {
        if ($event->hasGroup('system')) {
            $this->schemata = new SchemaCollection([]);
            $this->load($GLOBALS['TCA'], true);
        }
    }
}
