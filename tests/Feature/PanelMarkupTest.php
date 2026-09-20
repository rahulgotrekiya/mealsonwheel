<?php

namespace Tests\Feature;

use Tests\TestCase;

class PanelMarkupTest extends TestCase
{
    /**
     * Element ids in the panel must not collide with ones the theme styles.
     *
     * The stylesheet carries bare id selectors for its own furniture — the
     * preloader spinner is `#status`, for instance, and is positioned
     * absolutely at the centre of the screen. Naming a form control `status`
     * silently inherits all of that: the field detaches from its own form and
     * lands somewhere else on the page, with nothing failing and no console
     * error to follow.
     *
     * Ids the theme's own markup legitimately uses are listed as expected.
     */
    public function test_no_panel_element_id_collides_with_the_theme_stylesheet(): void
    {
        $themeIds = $this->idsStyledByTheme();

        $this->assertNotEmpty($themeIds, 'no theme stylesheets were read');

        // Furniture copied from the theme on purpose, which must keep its id.
        $intentional = ['page-topbar', 'scrollbar', 'navbar-nav', 'back-to-top'];

        $collisions = [];

        foreach ($this->panelViewFiles() as $file) {
            preg_match_all('/id="([a-zA-Z][\w-]*)"/', file_get_contents($file), $matches);

            foreach (array_unique($matches[1]) as $id) {
                if (in_array($id, $intentional, true)) {
                    continue;
                }

                if (in_array($id, $themeIds, true)) {
                    $collisions[] = basename($file).' uses id="'.$id.'"';
                }
            }
        }

        $this->assertSame(
            [],
            $collisions,
            "These ids are already styled by the panel stylesheet:\n  ".implode("\n  ", $collisions)
        );
    }

    /**
     * @return array<int, string>
     */
    private function idsStyledByTheme(): array
    {
        $ids = [];

        foreach (glob(public_path('assets/panel/css/*.css')) as $stylesheet) {
            // Only selectors that begin an id, so `.card#foo` style descendants
            // and colour literals such as #f06548 are left out.
            preg_match_all('/(?:^|[,{}])\s*#([a-zA-Z][\w-]*)/', file_get_contents($stylesheet), $matches);

            $ids = array_merge($ids, $matches[1]);
        }

        return array_values(array_unique($ids));
    }

    /**
     * @return array<int, string>
     */
    private function panelViewFiles(): array
    {
        $files = [];

        foreach (['admin', 'merchant', 'partials/panel'] as $directory) {
            $path = resource_path('views/'.$directory);

            if (! is_dir($path)) {
                continue;
            }

            $files = array_merge($files, $this->bladeFilesIn($path));
        }

        $files[] = resource_path('views/layouts/panel.blade.php');

        return array_filter($files, 'is_file');
    }

    /**
     * @return array<int, string>
     */
    private function bladeFilesIn(string $path): array
    {
        $found = [];

        foreach (new \RecursiveIteratorIterator(new \RecursiveDirectoryIterator($path)) as $file) {
            if ($file->isFile() && str_ends_with($file->getFilename(), '.blade.php')) {
                $found[] = $file->getPathname();
            }
        }

        return $found;
    }
}
