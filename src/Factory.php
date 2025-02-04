<?php

declare(strict_types=1);

namespace Koffin\Menu;

use Closure;
use Exception;
use Illuminate\Support\Fluent;
use Koffin\Menu\Enum\MenuType;

class Factory implements \Koffin\Menu\Contracts\Menu
{
    private static string $name;
    private static string $group;
    private static ?MenuItemAttribute $groupAttribute = null;
    private static ?Fluent $factory = null;
    private static string $childName;

    /**
     * @param  string|null  $name
     * @param  string|null  $group
     * @param  array  $groupAttribute
     */
    public function __construct(?string $name = null, ?string $group = null, array $groupAttribute = [])
    {
        static::$name = $name ?? 'main';
        static::$group = $group ?? 'Default';
        static::$groupAttribute = new MenuItemAttribute($groupAttribute);
        if (! static::$factory instanceof Fluent) {
            static::$factory = new Fluent();
        }
    }

    /**
     * @param  bool  $grouped
     * @param  bool  $resolvedOnly
     * @return \Koffin\Menu\MenuCollection
     */
    public function get(bool $grouped = true, bool $resolvedOnly = true): MenuCollection
    {
        try {
            if (static::$factory[static::$name] instanceof MenuCollection) {
                $menus = static::$factory[static::$name];

                if ($resolvedOnly) {
                    $menus = $menus->filter(fn ($m) => $m->resolve());
                }

                if ($grouped) {
                    $menus = $menus->groupBy('group');
                }

                return $menus;
            }
        } catch (Exception $e) {
        }

        return new MenuCollection();
    }

    /**
     * @param string $name
     * @param string $title
     * @param array $attribute
     * @param Closure|array $param
     * @param string|null $activeRoute
     * @param array|null $activeRouteParam
     * @param \Closure|bool $resolver
     * @param bool $hasChild
     * @return static
     */
    public static function route(
        string $name, string $title,
        array $attribute = [], Closure|array $param = [],
        ?string $activeRoute = null, ?array $activeRouteParam = null,
        Closure|bool $resolver = true, bool $hasChild = false
    ): static {
        return static::add(
            type: MenuType::ROUTE,
            name: $name,
            title: $title,
            attribute: $attribute,
            param: $param,
            activeRoute: $activeRoute,
            activeRouteParam: $activeRouteParam,
            resolver: $resolver,
        );
    }

    /**
     * @param  string  $name
     * @param  string  $title
     * @param  array  $attribute
     * @param  array  $param
     * @param  string|null  $activeRoute
     * @param  array|null  $activeRouteParam
     * @param  \Closure|bool  $resolver
     * @param  bool  $hasChild
     * @return static
     */
    public static function url(
        string $name, string $title,
        array $attribute = [], array $param = [],
        ?string $activeRoute = null, ?array $activeRouteParam = null,
        Closure|bool $resolver = true, bool $hasChild = false
    ): static {
        return static::add(
            type: MenuType::URL,
            name: $name,
            title: $title,
            attribute: $attribute,
            param: $param,
            activeRoute: $activeRoute,
            activeRouteParam: $activeRouteParam,
            resolver: $resolver,
        );
    }

    /**
     * @param  \Koffin\Menu\Enum\MenuType  $type
     * @param  string  $name
     * @param  string  $title
     * @param  array  $attribute
     * @param  array  $param
     * @param  string|null  $activeRoute
     * @param  array|null  $activeRouteParam
     * @param  \Closure|bool  $resolver
     * @return static
     */
    public static function add(
        MenuType $type, string $name, string $title,
        array $attribute = [], Closure|array $param = [],
        ?string $activeRoute = null, ?array $activeRouteParam = null,
        Closure|bool $resolver = true
    ): static {
        $factory = static::getFactory();
        $factory->add(
            new MenuItem(
                type: $type,
                title: $title,
                name: $name,
                param: $param,
                attribute: $attribute,
                activeRoute: $activeRoute,
                activeRouteParam: $activeRouteParam,
                group: static::$group,
                groupAttribute: static::$groupAttribute,
                resolver: $resolver,
            )
        );

        return new static(name: static::$name, group: static::$group);
    }

    /**
     * @return \Koffin\Menu\MenuCollection
     */
    private static function getFactory(): MenuCollection
    {
        if (! static::$factory[static::$name] instanceof MenuCollection) {
            static::$factory[static::$name] = new MenuCollection();
        }

        return static::$factory[static::$name];
    }
}
