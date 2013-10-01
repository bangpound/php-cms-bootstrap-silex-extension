<?php

namespace Bangpound\Silex;

use Silex\Application;
use Silex\ServiceProviderInterface;
use Symfony\Component\HttpFoundation\Request;

class PhpCmsBootstrapProvider implements ServiceProviderInterface
{

    /**
     * Registers services on the given app.
     *
     * This method should only be used to configure services and parameters.
     * It should not get services.
     *
     * @param Application $app An Application instance
     */
    public function register(Application $app)
    {
        $app['php.drupal7.bootstrap'] = $app->protect(function ($url = 'default', $pre_bootstrap = '', $post_bootstrap = '') use ($app) {
            $url = str_replace("'", "\\'", $url);
            return <<<EOT
define('DRUPAL_ROOT', getcwd());
require_once DRUPAL_ROOT . '/includes/bootstrap.inc';
drupal_override_server_variables(array('url' => '$url'));
$pre_bootstrap
drupal_bootstrap(DRUPAL_BOOTSTRAP_FULL);
$post_bootstrap
EOT;
        });

        $app['php.wordpress36.bootstrap'] = $app->protect(function ($pre_bootstrap = '', $post_bootstrap = '') use ($app) {
            return <<<EOT
$pre_bootstrap
\$wp_did_header = true;
require_once( dirname(__FILE__) . '/wp-load.php' );
wp();
$post_bootstrap
EOT;
        });
    }

    /**
     * Bootstraps the application.
     *
     * This method is called after all services are registered
     * and should be used for "dynamic" configuration (whenever
     * a service must be requested).
     */
    public function boot(Application $app)
    {
        // TODO: Implement boot() method.
    }
}
