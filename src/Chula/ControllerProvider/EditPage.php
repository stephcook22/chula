<?php
namespace Chula\ControllerProvider;

use Chula\Form\PageType;
use Chula\Service\Page as PageService;
use Chula\Tools\Encryption;
use Chula\Tools\StringManipulation;
use Silex\Application;
use Silex\ControllerProviderInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class EditPage implements ControllerProviderInterface
{

    public function connect(Application $app)
    {
        $controllers = $app['controllers_factory'];

        $form = $app['form.factory']->create(new PageType());

        $controllers->get(
            '/{slug}/{status}',
            function ($slug, $status) use ($app, $form) {
                if (!isset($app['config']['location'][$status])) {
                    return new Response('That status does not exist', 404);
                }

                $pageService = new PageService($app['config']);
                $page = $pageService->getPageFromSlugAndType($slug, $status);

                $form->get('slug')->setData($page->getSlug());
                $form->get('content')->setData($page->getContent());

                return $app['twig']->render('admin_edit_page.twig', array('form' => $form->createView()));
            }
        )->bind('admin_edit');

        $controllers->post(
            '/{page}/{status}',
            function ($page, $status, Request $request) use ($app, $form) {
                if (!isset($app['config']['location'][$status])) {
                    return new Response('That status does not exist', 404);
                }
                $form->bind($request);

                if ($form->isValid()) {
                    $data = $form->getData();
                    $content = ($app['config']['encrypt']) ? Encryption::encrypt($data['content']) : $data['content'];

                    $slug = StringManipulation::toSlug($data['slug']);
                    if ($slug != $page) {
                        unlink($app['config']['location'][$status] . $page);
                    }

                    file_put_contents($app['config']['location'][$status] . $slug, $content, LOCK_EX);

                    return $app->redirect($app['url_generator']->generate('admin'));
                }
                return $app['twig']->render('admin_edit_page.twig', array('form' => $form->createView()));
            }
        );

        return $controllers;

    }
}