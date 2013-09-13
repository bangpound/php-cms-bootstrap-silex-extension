<?php

namespace Bangpound\Silex\Test;

use Silex\Application;
use Bangpound\Silex\PhpCmsBootstrapProvider;
use Symfony\Component\Process\PhpProcess;
use Symfony\Component\Process\Process;

class PhpCmsBootstrapProviderTest extends \PHPUnit_Framework_TestCase
{
    public function testDrushBootstrap()
    {
        $app = $this->createApplication();
        $commandline = realpath('./vendor/bin/drush');
        $process = new Process($commandline . ' core-status version --pipe');
        $process->setWorkingDirectory(realpath('./vendor/drupal/drupal'));
        $process->run();
        $output = json_decode($process->getOutput(), true);
        $this->assertArrayHasKey('drupal-version', $output);
        $this->assertArrayHasKey('drush-version', $output);
    }

    public function createApplication()
    {
        $app = new Application();
        $app->register(new PhpCmsBootstrapProvider());
        return $app;
    }

    public function testDrupalBootstrap()
    {
        $app = $this->createApplication();
        $commandline = realpath('./vendor/bin/drush');
        $site = md5(microtime());
        $process = new \Symfony\Component\Process\Process($commandline . ' --yes site-install --db-url=sqlite://sites/' . $site . '/files/.ht.sqlite --sites-subdir=' . $site . ' --pipe');
        $process->setWorkingDirectory(realpath('./vendor/drupal/drupal'));
        $process->run();
        $output = $process->getOutput();

        $script = $app['php.drupal7.bootstrap']('http://' . $site . '/index.php', '', 'echo conf_path();');
        $process = new PhpProcess('<?php ' . $script, realpath('./vendor/drupal/drupal'));
        $process->run();
        $output = $process->getOutput();
        $this->assertEquals('sites/' . $site, $output);
    }

    public function testWordpressBootstrap()
    {
        $app = $this->createApplication();
        $commandline = realpath('./vendor/wp-cli/wp-cli/bin/wp core config');
        $process = new \Symfony\Component\Process\Process($commandline);
        $process->setWorkingDirectory(realpath('./vendor/wordpress/wordpress'));
        $process->run();
        $output = $process->getOutput();

        $script = $app['php.wordpress36.bootstrap']();
        $process = new \Symfony\Component\Process\PhpProcess('<?php ' . $script, realpath('./vendor/wordpress/wordpress'));
        $process->setWorkingDirectory(realpath('./vendor/wordpress/wordpress'));
        $process->run();
        $output = $process->getOutput();
    }

}
