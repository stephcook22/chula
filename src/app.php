<?php
require_once __DIR__ . '/bootstrap.php';


use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

$app->before(function () use ($app) {
    foreach ($app['config']['location'] as $path) {
        if (!is_dir($path)) {
            throw new \Exception("There was an issue loading the content. Is your content location correct?");
        }
    }
});


$app->mount('/', new Chula\ControllerProvider\HomePage());

$app->mount(
    '/' . $app['config']['admin_path'],
    new Chula\ControllerProvider\Admin()
);

$app->mount(
    '/{page}',
    new Chula\ControllerProvider\Loader()
);

$app->mount(
    '/' . $app['config']['admin_path'] . '/new',
    new Chula\ControllerProvider\NewPage()
);

$app->mount(
    '/' . $app['config']['admin_path'] . '/delete',
    new Chula\ControllerProvider\DeletePage()
);

$app->mount(
    '/' . $app['config']['admin_path'] . '/edit',
    new Chula\ControllerProvider\EditPage()
);

$app->mount(
    '/' . $app['config']['admin_path'] . '/publish',
    new Chula\ControllerProvider\PublishDraft()
);


$app->get(
    '/login',
    function (Request $request) use ($app) {
        return $app['twig']->render(
            'admin_login.twig',
            array(
              'error'         => $app['security.last_error']($request),
              'last_username' => $app['session']->get('_security.last_username'),
            )
        );
    }
);

$app->error(function (\Exception $e, $code) use ($app) {
    switch ($code) {
        case 404:
            $message = 'Those monkeys couldn\'t find the page you were after, hard luck.';
            return $app['twig']->render('user_404.twig', array('message' => $message));
            break;
        default:
            $message = $e->getMessage();
            return $app['twig']->render('user_error.twig', array('message' => $message));
    }
});

$app->run();
