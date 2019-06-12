<?php

namespace KbProducts\Commands;


use KbProducts\Core\Import;
use Shopware\Components\Model\ModelManager;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Shopware\Commands\ShopwareCommand;

class ImportCommand extends ShopwareCommand
{
    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this->setName('kb:butsch:update')
            ->setDescription('StockUpdate')
            ->addArgument(
                'mode',
                InputArgument::REQUIRED,
                'Importtype.'
            );
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {

        $logger = $this->container->get('pluginlogger');
        $mode = $input->getArgument('mode');

        $import = New Import($output,$logger,$mode);

        $import->run();
    }
}
