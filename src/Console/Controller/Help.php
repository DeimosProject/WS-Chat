<?php

namespace Deimos\Console\Controller;

use Deimos\WebSocket\Controller;
use phpDocumentor\Reflection\DocBlockFactory;
use Symfony\Component\Console\Helper\Table;
use Symfony\Component\Console\Helper\TableSeparator;
use Symfony\Component\Console\Helper\TableStyle;
use Symfony\Component\Console\Output\BufferedOutput;

class Help extends Controller
{

    protected function scan()
    {
        return array_filter(scandir(__DIR__), function ($file)
        {
            return
                is_file(__DIR__ . '/' . $file) &&
                !in_array($file, ['.', '..', basename(__FILE__)]);
        });
    }

    protected function actionDefault()
    {
        $factory = DocBlockFactory::createInstance();

        $results = [];

        foreach ($this->scan() as $className)
        {
            $className  = substr($className, 0, -4);
            $reflection = new \ReflectionClass(__NAMESPACE__ . '\\' . $className);
            $className  = lcfirst($className);

            foreach ($reflection->getMethods() as $method)
            {
                if (0 !== strpos($method->name, 'action'))
                {
                    continue;
                }

                $actionName = lcfirst(substr($method->name, 6));

                $docs = '-';

                if ($method->getDocComment())
                {
                    $docs = $factory->create($method->getDocComment())->getSummary() . PHP_EOL;
                }

                $results[] = [
                    'command' => $className . ($actionName === 'default' ? '' : ':' . $actionName),
                    'summary' => $docs
                ];
            }
        }

        $buffer = new BufferedOutput();
        $table  = new Table($buffer);

        if (!empty($results))
        {
            $table->setHeaders(['COMMAND', 'SUMMARY']);
        }

        $table->setRows($results);
        $table->render();

        return $buffer->fetch();
    }

}
