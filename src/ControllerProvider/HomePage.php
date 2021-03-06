<?php

namespace Chula\ControllerProvider;

use Chula\Tools\Encryption;
use Chula\Tools\FileManipulation;
use Michelf\Markdown;
use Silex\Application;
use Silex\ControllerProviderInterface;

class HomePage implements ControllerProviderInterface
{

    public function connect(Application $app)
    {
        $controllers = $app['controllers_factory'];


        $controllers->get(
          '/',
          function () use ($app) {

              if (isset($app['config']['homepage_type']) && $app['config']['homepage_type'] != 'list') {
                  return $app['twig']->render('user_home.twig');
              }
              // grab all items in our content dir
              $pageNames = array();
              if (file_exists($app['config']['location']['published'])) {
                  $pageNames = FileManipulation::listDirByDate($app['config']['location']['published']);
              }

              $pages = array();

              //@todo this should be in a service
              foreach ($pageNames as $timeKey => $page) {
                  $content = file_get_contents($app['config']['location']['published'] . '/' . $page);

                  $timeArray = explode(",", $timeKey);
                  if ($app['config']['encrypt']) {
                      // Need to decrypt the content first if we're set to use encryption
                      $content = Encryption::decrypt($content);
                  }

                  $html = Markdown::defaultTransform($content);

                  $pages[$page]['slug']    = $page;
                  $pages[$page]['content'] = $html;
                  $pages[$page]['time'] = $timeArray[0];
              }

              return $app['twig']->render('user_home.twig', array('pages' => $pages));


          }
        )->bind('home');

        return $controllers;
    }

}