<?php
/**
 * This source file is part of the open source project
 * ExpressionEngine (https://expressionengine.com)
 *
 * @link      https://expressionengine.com/
 * @copyright Copyright (c) 2003-2022, Packet Tide, LLC (https://www.packettide.com)
 * @license   https://expressionengine.com/license Licensed under Apache License, Version 2.0
 */

namespace ExpressionEngine\Service\Addon\Controllers\Mcp;

use ExpressionEngine\Service\Addon\Controllers\AbstractRoute as CoreAbstractRoute;
use ExpressionEngine\Service\Addon\Exceptions\Controllers\Mcp\RouteException;
use ExpressionEngine\Service\Sidebar\BasicList;
use ExpressionEngine\Service\Sidebar\Header;

abstract class AbstractRoute extends CoreAbstractRoute
{
    /**
     * @var string
     */
    protected $route_path = '';

    /**
     * The Control Panel Heading text
     * @var string
     */
    protected $heading = '';

    /**
     * The raw HTML body for the Control Panel view
     * @var string
     */
    protected $body = ' ';

    /**
     * An array of urls => text for breadcrumbs
     * @var array
     */
    protected $breadcrumbs = [];

    /**
     * @var int
     */
    public $per_page = 25;

    /**
     * @var string
     */
    protected $base_url = '';

    /**
     * @var ExpressionEngine\Service\Sidebar\BasicItem
     */
    protected $sidebarItem = null;


    /**
     * @var Sidebar
     */
    protected $sidebar;

    public function __construct()
    {
    }

    /**
     * @return AbstractRoute
     */
    abstract public function process($id = false);

    /**
     * @return string
     */
    public function getHeading()
    {
        return $this->heading;
    }

    /**
     * @param string $heading
     * @return $this
     */
    public function setHeading($heading)
    {
        $this->heading = $heading;

        return $this;
    }

    /**
     * @return string
     */
    public function getBody()
    {
        return $this->body;
    }

    /**
     * @param string $body HTML body or name of view
     * @param array $variables
     * @return $this
     */
    public function setBody($body, array $variables = [])
    {
        // If $variables were passed, then we can assume they are setting a view
        if (!empty($variables)) {
            return $this->setView($body, $variables);
        }

        // If it wasnt a view, we just assume it's the html body
        $this->body = $body;

        return $this;
    }

    /**
     * @param string $view
     * @return $this
     */
    public function setView($view, array $variables = [])
    {
        // If they didnt pass a view with a ':', lets assume its an addon view
        if (! ee('Str')->string_contains($view, ':')) {
            $view = $this->addon_name . ':' . $view;
        }

        $variables = $this->prepareBodyVars($variables);
        $this->body = ee('View')->make($view)->render($variables);

        return $this;
    }

    /**
     * Compiles some universal variables for use in views
     * @param array $variables
     */
    protected function prepareBodyVars(array $variables = [])
    {
        return array_merge([
            'cp_page_title' => $this->getHeading(),
            'base_url' => $this->getBaseUrl(),
        ], $variables);
    }

    /**
     * @return array
     */
    public function getBreadcrumbs()
    {
        return $this->breadcrumbs;
    }

    /**
     * @param string $url
     * @param string $text
     * @return $this
     */
    protected function addBreadcrumb($url, $text)
    {
        $this->breadcrumbs[$this->url($url, true)] = lang($text);

        return $this;
    }

    /**
     * @param array $breadcrumbs
     * @return $this
     */
    protected function setBreadcrumbs(array $breadcrumbs = [])
    {
        $this->breadcrumbs = $breadcrumbs;

        return $this;
    }

    public function processSidebar()
    {
        $sidebarClass = $this->getRouteNamespace() . '\Mcp\Sidebar';

        // Check to see if the sidebar class exists. If not, return this
        if (! class_exists($sidebarClass)) {
            return $this;
        }

        // Process the sidebar
        $this->sidebar = new $sidebarClass($this->getAddonName(), $this->getRouteNamespace());
        $this->sidebar->process();

        // Lets get the active item, set is to current, and then set it to active
        foreach ($this->sidebar->getSidebar()->getItems() as $sidebarHeader) {
            // If this list is empty, no need to search it for the item
            if (!$sidebarHeader instanceof \Header) {
                continue;
            }

            $basicList = $sidebarHeader->getList();

            // If this list is empty, no need to search it for the item
            if (empty($basicList) || !$basicList instanceof \BasicList) {
                continue;
            }

            // Get the sidebar item based on the url
            $this->sidebarItem = $basicList->getItemByUrl($this->url($this->route_path));
            $this->sidebarItem->isActive();
        }

        return $this;
    }

    /**
     * @param string $path
     * @param bool $with_base
     * @param array $query
     * @return mixed
     */
    protected function url($path, $with_base = true, $query = [])
    {
        if ($with_base) {
            $path = $this->getBaseUrl() . '/' . $path;
        }

        return ee('CP/URL')->make($path, $query)->compile();
    }

    /**
     * @return
     */
    public function getCurrentSidebarItem()
    {
        if (is_null($this->sidebarItem) && isset($this->sidebar->routes[$this->route_path])) {
            $this->sidebarItem = $this->sidebar->routes[$this->route_path];
        }

        return $this->sidebarItem;
    }

    public function getSidebarItems()
    {
        return $this->sidebar->routes;
    }

    /**
     * @return array
     */
    public function toArray()
    {
        return [
            'heading' => lang($this->getHeading()),
            'breadcrumb' => $this->getBreadcrumbs(),
            'body' => $this->getBody(),
        ];
    }

    /**
     * @param mixed $id
     * @return string
     * @throws RouteException
     */
    protected function getRoutePath($id = '')
    {
        if ($this->route_path == '') {
            throw new RouteException("Your route_path property isn't setup in your Route object!");
        }

        return $this->route_path . ($id !== false && $id != '' ? '/' . $id : '');
    }

    /**
     * @return string
     */
    public function getBaseUrl()
    {
        if ($this->base_url == '') {
            $this->base_url = 'addons/settings/' . $this->getAddonName();
        }

        return $this->base_url;
    }
}
