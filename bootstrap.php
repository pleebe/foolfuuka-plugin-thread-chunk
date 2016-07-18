<?php

use Doctrine\DBAL\Schema\Schema;
use Foolz\FoolFrame\Model\Autoloader;
use Foolz\FoolFrame\Model\Context;
use Foolz\FoolFrame\Model\Preferences;
use Foolz\FoolFrame\Model\DoctrineConnection;
use Foolz\FoolFrame\Model\Plugins;
use Foolz\FoolFrame\Model\Uri;
use Foolz\FoolFuuka\Model\RadixCollection;
use Foolz\Plugin\Event;
use Symfony\Component\Routing\Route;

class HHVM_thread_chunk
{
    public function run()
    {
        Event::forge('Foolz\Plugin\Plugin::execute#foolz/foolfuuka-plugin-thread-chunk')
            ->setCall(function ($plugin) {
                /** @var Context $context */
                $context = $plugin->getParam('context');
                /** @var Autoloader $autoloader */
                $autoloader = $context->getService('autoloader');
                $autoloader->addClassMap([
                    'Foolz\FoolFuuka\Controller\Chan\ThreadChunk' => __DIR__ . '/classes/controller/chan.php',
                    'Foolz\FoolFuuka\Plugins\ThreadChunk\Model\ThreadChunk' => __DIR__ . '/classes/model/chunk.php',
                ]);

                $context->getContainer()
                    ->register('foolfuuka-plugin.chunk', 'Foolz\FoolFuuka\Plugins\ThreadChunk\Model\ThreadChunk')
                    ->addArgument($context);

                Event::forge('Foolz\FoolFrame\Model\Context::handleWeb#obj.routing')
                    ->setCall(function ($result) use ($context) {
                        $routes = $result->getObject();
                        $radix_collection = $context->getService('foolfuuka.radix_collection');
                        $radices = $radix_collection->getAll();
                        foreach ($radices as $radix) {
                            $routes->getRouteCollection()->add(
                                'foolfuuka.plugin.thread-chunk.chan.radix.' . $radix->shortname, new Route(
                                '/' . $radix->shortname . '/chunk/{_suffix}',
                                [
                                    '_controller' => '\Foolz\FoolFuuka\Controller\Chan\ThreadChunk::chunk',
                                    '_default_suffix' => '',
                                    '_suffix' => '',
                                    'radix_shortname' => $radix->shortname
                                ],
                                [
                                    '_suffix' => '.*'
                                ]
                            ));
                        }
                    });
            });
    }
}


(new HHVM_thread_chunk())->run();
