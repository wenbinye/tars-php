<?php

declare(strict_types=1);

namespace wenbinye\tars\server\task;

use Doctrine\Common\Annotations\Reader;
use Psr\Container\ContainerInterface;
use wenbinye\tars\server\annotation\TaskHandler;
use wenbinye\tars\server\exception\TaskHandlerNotFoundException;
use wenbinye\tars\server\SwooleServer;

class Queue implements QueueInterface, TaskProcessorInterface
{
    /**
     * @var SwooleServer
     */
    private $server;

    /**
     * @var Reader
     */
    private $annotationReader;

    /**
     * @var ContainerInterface
     */
    private $container;

    /**
     * @var TaskHandlerInterface[]
     */
    private $taskHandlers;

    public function __construct(SwooleServer $server, Reader $annotationReader, ContainerInterface $container)
    {
        $this->server = $server;
        $this->annotationReader = $annotationReader;
        $this->container = $container;
    }

    /**
     * {@inheritdoc}
     */
    public function put($task, int $workerId = -1, callable $onFinish = null): int
    {
        $this->resolveHandler($task);

        return $this->server->getSwooleServer()->task($task, $workerId, $onFinish);
    }

    public function registerTaskHandler(string $taskClass, $handler): void
    {
        if (is_string($handler)) {
            $handler = $this->container->get($handler);
        }
        if (!($handler instanceof TaskHandlerInterface)) {
            throw new \InvalidArgumentException("task handler '".get_class($handler)."' should implement ".TaskHandlerInterface::class);
        }
        $this->taskHandlers[$taskClass] = $handler;
    }

    /**
     * {@inheritdoc}
     */
    public function process($task): void
    {
        $result = $this->resolveHandler($task)->handle($task);
        if (isset($result)) {
            $this->server->getSwooleServer()->finish($result);
        }
    }

    public function resolveHandler(object $task): TaskHandlerInterface
    {
        $taskClass = get_class($task);
        if (!isset($this->taskHandlers[$taskClass])) {
            /** @var TaskHandler $annotation */
            $annotation = $this->annotationReader->getClassAnnotation(new \ReflectionClass($taskClass), TaskHandler::class);
            if ($annotation) {
                $this->registerTaskHandler($taskClass, $annotation->name);
            } else {
                $handler = $taskClass.'Handler';
                if (class_exists($handler)) {
                    $this->registerTaskHandler($taskClass, $handler);
                } else {
                    throw new TaskHandlerNotFoundException('Cannot find task handler for task '.$taskClass);
                }
            }
        }

        return $this->taskHandlers[$taskClass];
    }
}
