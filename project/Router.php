<?php

namespace app;

abstract class Router
{
    private static $_routes;

    public static function register($rules, $action)
    {
        $rules = (array) $rules;

        foreach ($rules as $rule) {
            if (!isset(self::$_routes[$rule])) {
                list($method, $rule) = explode(' ', $rule);

                $preparedRule = self::prepareRule($rule);

                self::$_routes[$preparedRule] = [
                    'verb' => strtolower($method),
                    'action' => $action
                ];
            }
        }
    }

    /**
     * @param string $request
     * @return string mixed
     * @throws \Exception
     */
    public static function process($request)
    {
        $urlParts = parse_url($request);

        $path = trim($urlParts['path'], '/');
        $params = [];

        foreach (self::$_routes as $rule => $ruleData) {
            $method = $ruleData['verb'];
            $class = $ruleData['action'];

            if ($method !== strtolower($_SERVER['REQUEST_METHOD'])) {
                continue;
            }

            if (preg_match($rule, $path, $matches)) {

                $params = array_filter(
                    $matches,
                    function ($key) use ($matches) {
                        return !is_numeric($key);
                    },
                    ARRAY_FILTER_USE_KEY
                );

                return call_user_func_array([new $class, 'run'], $params);
            }
        }

        throw new \Exception(404);
    }

    private static function prepareRule($rule)
    {
        $rule = trim($rule, '/');

        $rule = '/' . $rule . '/';

        $tr = [
            '.' => '\\.',
            '*' => '\\*',
            '$' => '\\$',
            '[' => '\\[',
            ']' => '\\]',
            '(' => '\\(',
            ')' => '\\)',
        ];

        if (preg_match_all('/<(\w+):?([^>]+)?>/', $rule, $matches, PREG_OFFSET_CAPTURE | PREG_SET_ORDER)) {
            foreach ($matches as $match) {
                $name = $match[1][0];
                $pattern = isset($match[2][0]) ? $match[2][0] : '[^\/]+';
                $tr["<$name>"] = "(?P<$name>$pattern)";
            }
        }

        $template = preg_replace('/<(\w+):?([^>]+)?>/', '<$1>', $rule);
        $rule = '#^' . trim(strtr($template, $tr), '/') . '$#u';

        return $rule;
    }
}