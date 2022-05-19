<?php

namespace ComposerCost;

use Composer\Composer;
use Composer\EventDispatcher\EventSubscriberInterface;
use Composer\IO\IOInterface;
use Composer\Plugin\PluginInterface;
use Composer\Script\Event;
use Composer\Script\ScriptEvents;

class ComposerCost implements PluginInterface, EventSubscriberInterface
{
    protected $io;
    protected $composer;

     public function uninstall(Composer $composer, IOInterface $io)
    {
    }
    
    /**
     * {@inheritdoc}
     */
    public function activate(Composer $composer, IOInterface $io)
    {
        $this->composer = $composer;
        $this->io = $io;
    }

    /**
     * {@inheritdoc}
     */
    public static function getSubscribedEvents()
    {
        return [
            ScriptEvents::POST_INSTALL_CMD => [['postInstall']],
            ScriptEvents::POST_UPDATE_CMD => [['postUpdate']],
        ];
    }

    /**
     * @param Event $event
     */
    public function postInstall(Event $event)
    {
        $this->calculate();
    }

    /**
     * @param Event $event
     */
    public function postUpdate(Event $event)
    {
        $this->calculate();
    }

    /**
     * Calculate and show package sizes
     */
    private function calculate()
    {
        $total = 0;
        $folders = [];
        $sizes = [];
        $vendorDir = getcwd() . DIRECTORY_SEPARATOR . 'vendor';

        if (file_exists($vendorDir) && is_readable($vendorDir)) {
            foreach (glob($vendorDir . '/*', GLOB_NOSORT) as $path) {
                if (!is_file($path)) {
                    $total += $this->dirSize($path);
                    $sizes[basename($path)] = $this->dirSize($path);
                }
            }
        }

        // sort by bigger size first
        uasort($sizes, static function ($a, $b) {
            return $b - $a;
        });

        foreach ($sizes as $folder => $size) {
            $folders[] = ['Folder' => $folder, 'Size' => $this->humanSize($size)];
        }
        
        // total
        $folders[] = ['Folder' => 'TOTAL', 'Size' => $this->humanSize($total)];

        $this->io->write('');
        $this->io->write('<fg=black;bg=green> Vendor Cost </>');
        $this->table($folders);
    }

    private function dirSize($rootDir)
    {
        $size = 0;

        foreach (glob(rtrim($rootDir, '/') . '/*', GLOB_NOSORT) as $path) {
            $size += is_file($path) ? filesize($path) : $this->dirSize($path);
        }

        return $size;
    }

    private function humanSize($fsizebyte)
    {
        $size = 0;

        if ($fsizebyte < 1024) {
            $size = $fsizebyte . ' bytes';
        } elseif (($fsizebyte >= 1024) && ($fsizebyte < 1048576)) {
            $size = round(($fsizebyte / 1024), 2);
            $size .= ' KB';
        } elseif (($fsizebyte >= 1048576) && ($fsizebyte < 1073741824)) {
            $size = round(($fsizebyte / 1048576), 2);
            $size .= ' MB';
        } elseif ($fsizebyte >= 1073741824) {
            $size = round(($fsizebyte / 1073741824), 2);
            $size .= ' GB';
        }

        return $size;
    }

    private function table($data)
    {
        $keys = array_keys(end($data));
        $size = array_map('strlen', $keys);

        foreach (array_map('array_values', $data) as $e) {
            $size = array_map('max', $size,
                array_map('strlen', $e));
        }

        foreach ($size as $n) {
            $form[] = "%-{$n}s";
            $line[] = str_repeat('-', $n);
        }

        $form = '| ' . implode(' | ', $form) . " |\n";
        $line = '+-' . implode('-+-', $line) . "-+\n";
        $rows = array(vsprintf($form, $keys));

        foreach ($data as $e) {
            $rows[] = vsprintf($form, $e);
        }

        $this->io->write('<fg=green>' . $line . implode($line, $rows) . $line . '</>');
    }
}
