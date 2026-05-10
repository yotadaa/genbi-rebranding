<?php

declare(strict_types=1);

namespace App\Core;

final class Router
{
    /** @var array<int, array{method: string, pattern: string, handler: callable, middleware: Middleware[]}> */
    private array $routes = [];

    /** @var Middleware[] */
    private array $groupMiddleware = [];

    /** @var Middleware[] */
    private array $globalMiddleware = [];

    public function get(string $pattern, callable $handler): void
    {
        $this->add('GET', $pattern, $handler);
    }

    public function post(string $pattern, callable $handler): void
    {
        $this->add('POST', $pattern, $handler);
    }

    /** @param Middleware[] $middleware */
    public function group(array $middleware, callable $callback): void
    {
        $previous = $this->groupMiddleware;
        $this->groupMiddleware = array_merge($this->groupMiddleware, $middleware);
        $callback($this);
        $this->groupMiddleware = $previous;
    }

    public function addGlobalMiddleware(Middleware $middleware): void
    {
        $this->globalMiddleware[] = $middleware;
    }

    private function add(string $method, string $pattern, callable $handler): void
    {
        $this->routes[] = [
            'method' => $method,
            'pattern' => $this->normalizePattern($pattern),
            'handler' => $handler,
            'middleware' => $this->groupMiddleware,
        ];
    }

    public function dispatch(Request $request, Response $response): void
    {
        try {
            $allowedMethodsForPath = [];
            foreach ($this->routes as $route) {
                $params = $this->match($route['pattern'], $request->path());
                if ($params !== null) {
                    $allowedMethodsForPath[] = $route['method'];
                }

                if (!$this->methodMatches($route['method'], $request->method())) {
                    continue;
                }

                if ($params === null) {
                    continue;
                }

                $handler = $route['handler'];
                $middleware = $route['middleware'];

                $pipeline = static function () use ($handler, $request, $response, $params): void {
                    call_user_func($handler, $request, $response, $params);
                };

                foreach (array_reverse(array_merge($this->globalMiddleware, $middleware)) as $mw) {
                    $next = $pipeline;
                    $pipeline = static function () use ($mw, $request, $response, $next): void {
                        $mw->handle($request, $response, $next);
                    };
                }

                $pipeline();
                return;
            }

            if ($allowedMethodsForPath !== []) {
                ErrorHandler::render($response, 405);
                return;
            }

            ErrorHandler::render($response, 404);
        } catch (\Throwable $error) {
            ErrorHandler::renderThrowable($response, $error);
        }
    }

    private function methodMatches(string $routeMethod, string $requestMethod): bool
    {
        return $routeMethod === $requestMethod || ($requestMethod === 'HEAD' && $routeMethod === 'GET');
    }

    private function normalizePattern(string $pattern): string
    {
        $pattern = '/' . trim($pattern, '/');

        return $pattern === '/' ? '/' : rtrim($pattern, '/');
    }

    /** @return array<string, string>|null */
    private function match(string $pattern, string $path): ?array
    {
        $keys = [];
        $parts = preg_split('/(\{[a-zA-Z_][a-zA-Z0-9_]*\})/', $pattern, -1, PREG_SPLIT_DELIM_CAPTURE);
        if ($parts === false) {
            return null;
        }

        $regex = '';
        foreach ($parts as $part) {
            if (preg_match('/^\{([a-zA-Z_][a-zA-Z0-9_]*)\}$/', $part, $matches)) {
                $keys[] = $matches[1];
                $regex .= '([^/]+)';
                continue;
            }

            $regex .= preg_quote($part, '#');
        }

        if (!preg_match('#^' . $regex . '$#', $path, $matches)) {
            return null;
        }

        array_shift($matches);
        $params = [];
        foreach ($keys as $index => $key) {
            $params[$key] = urldecode($matches[$index] ?? '');
        }

        return $params;
    }
}
