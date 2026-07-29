<?php

namespace digitalpulsebe\utmtracker\storage;

use Craft;
use craft\web\Request;
use digitalpulsebe\utmtracker\models\Parameters;

abstract class StorageMethod
{
    /**
     * Whether this is a first-time visitor (no prior stored data found).
     */
    protected bool $isNewUser = false;

    protected Parameters $parameters;

    protected bool $isLoaded = false;

    // =========================================================================
    // Abstract — subclasses handle the actual store
    // =========================================================================

    /**
     * Read parameters from the underlying store (cookie / session).
     * Sets $this->parameters and $this->isNewUser.
     * Must NOT write anything — reading must always be safe.
     */
    abstract protected function load(): void;

    /**
     * Persist the current $this->parameters to the underlying store.
     */
    abstract protected function persist(): void;

    // =========================================================================
    // Public init entry-points
    // =========================================================================

    /**
     * Load existing data from the store, merge query parameters from the
     * current site request, then persist.
     * Call this on synchronous (non-async) requests.
     */
    public function setFromRequest(Request $request): void
    {
        $this->load();
        $this->parameters->processQueryParametersFromRequest($request, $this->isNewUser);
        Craft::info(['params_set' => $this->getParameters()->toArray()], 'utm_tracker');
        $this->persist();
    }

    /**
     * Load existing data from the store, merge parameters parsed from a URL
     * string, then persist.
     * Call this from the async API endpoint after the browser posts the URL.
     */
    public function setFromUrl(string $url, string $referrerUrl = null): void
    {
        $this->load();
        $this->parameters->processParametersFromUrl($url, $referrerUrl);
        Craft::info(['params_set' => $this->getParameters()->toArray()], 'utm_tracker');
        $this->persist();
    }

    // =========================================================================
    // Public accessors
    // =========================================================================

    public function isNewUser(): bool
    {
        return $this->isNewUser;
    }

    public function getParameters(): Parameters
    {
        if (!$this->isLoaded) {
            // only load when we need to
            $this->load();
        }
        return $this->parameters;
    }
}
