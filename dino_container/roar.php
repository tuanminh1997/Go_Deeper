<?php

namespace Dino\Play;


use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\Reference;
use Symfony\Component\Routing\Loader\YamlFileLoader;

require __DIR__.'/../vendor/autoload.php';

$container= new ContainerBuilder();

$loader=new YamlFileLoader($container,new FileLocator(__DIR__.'/config'));
$loader->load('service.yml');

/*$loggerDefinition=new Definition('Monolog\Logger');
$loggerDefinition->setArguments(array(
    'main',
    array(new Reference('logger.stream_handler'))
));

$loggerDefinition->addMethodCall('pushHandler',array(
    new Reference('logger.std_out_logger')
));


$loggerDefinition->addMethodCall('debug', array(
    'The logger just got stated'
));

$container->setDefinition('logger', $loggerDefinition);
*/x

$handlerDefinition= new Definition('Monolog\Handler\StreamHandler');
$handlerDefinition->setArguments(array(
    __DIR__.'/dino.log'
));

$container->setDefinition('logger.stream_handler',$handlerDefinition);


$stdOutLoggerDefinition=new Definition('Monolog\Handler\StreamHandler');
$stdOutLoggerDefinition->setArguments(array(
   'php://stdout'
));
$container->setDefinition('logger.std_out_logger',$stdOutLoggerDefinition);
 runApp($container);
function runApp(ContainerBuilder $container)
{
    $container->get('logger')->info('ROOOOOAR');

}


