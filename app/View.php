<?php

class View
{
    public static function render(string $template, array $data = [], ?string $layout = 'layouts/app'): void
    {
        $viewFile = dirname(__DIR__) . '/views/' . $template . '.php';
        if (!is_file($viewFile)) {
            throw new RuntimeException('View not found: ' . $template);
        }
        $vars = $data;
        unset($data, $template);
        extract($vars, EXTR_OVERWRITE);
        ob_start();
        include $viewFile;
        $content = ob_get_clean();
        if ($layout) {
            $layoutFile = dirname(__DIR__) . '/views/' . $layout . '.php';
            include $layoutFile;
        } else {
            echo $content;
        }
    }

    public static function partial(string $template, array $data = []): void
    {
        extract($data, EXTR_SKIP);
        include dirname(__DIR__) . '/views/' . $template . '.php';
    }
}
