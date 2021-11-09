<?php


namespace App\Command;


use App\Controller\LogController;
use App\Entity\Log;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Contracts\HttpClient\Exception\ClientExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\RedirectionExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\ServerExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;
use Symfony\Component\Filesystem\Exception\IOExceptionInterface;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Console\Input\InputDefinition;
use Symfony\Component\Console\Input\InputOption;
use Doctrine\ORM\EntityManagerInterface;

class ScreenshotCommand extends Command
{

    public $commandName = 'app:screenshot';
    private $client;
    private $params;
    private $entityManager;
    private $token;

    public function __construct(HttpClientInterface $client, ParameterBagInterface $params, EntityManagerInterface $entityManager)
    {
        $this->client = $client;
        $this->params = $params;
        $this->entityManager = $entityManager;
        $this->token = $this->params->get("token_screenshot_api");

        parent::__construct();
    }

    protected function configure()
    {
        $this->setDescription('Take a screenshot and save the results and log')
            ->setName('app:screenshot')
            ->setDefinition(
                $this->configureInputDefinitions()
            );
    }

    public function arrangeQueryParams($input)
    {
        $query = '';
        $query = http_build_query($input->getOptions(), '', '&');
        return $query;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {

        $queryParams = $this->arrangeQueryParams($input);
        $logController = new LogController($this->params, $this->entityManager);

        if($input->getOption('url') == null) {
            //$response = $this->createLog($input, $queryParams, null);
            $response = $logController->store($input, $queryParams, null);
            $response = 'Command unsuccessful. Url not defined.' . ($response ? ' and log was stored' : " $response");
            $output->writeln($response);
            return Command::INVALID;
        }
try{
        $urlQuery = "https://shot.screenshotapi.net/screenshot" . "?token=$this->token&$queryParams";

        $response = $this->client->request(
            'GET',
            $urlQuery
        );

        try {
            $content = $response->getContent();
        } catch (\Exception $e) {
            $logController->store($input, $queryParams, null);
            //$response = $this->createLog($input, $queryParams, null);
            $response = 'Error in arguments. Command not successful' . ($response ? ' and log was stored' : ' and log was not stored');
            $output->writeln($response);
            return Command::INVALID;
        }

        $fileName = basename((json_decode($content)->screenshot));
        $filePath = $this->params->get("uploads_directory") . $fileName;

        file_put_contents($filePath, file_get_contents(json_decode($content)->screenshot));
        $logController = new LogController($this->params, $this->entityManager);
        $logController->store($input, $queryParams, $fileName, true);

        $response ? $output->writeln("Image stored successfully in $filePath and log was stored.") : $output->writeln("Image stored successfully in $filePath but log not saved.");

        $output->writeln('Command success');
        return Command::SUCCESS;
}catch(\Exception $e){
    $response = $logController->store($input, $queryParams, null);
    //$response = $this->createLog($input, $queryParams, null);
    $response = 'Error in arguments. Command not successful' . ($response ? ' and log was stored' : " $response");
    $output->writeln($response);
    return Command::INVALID;
}
    }

    public function configureInputDefinitions()
    {
        return new InputDefinition(
            [
                new InputOption(
                    'url',
                    'u',
                    InputOption::VALUE_REQUIRED,
                    'Url to take a screenshot from'
                ),
                new InputOption(
                    'file_type',
                    't',
                    InputOption::VALUE_OPTIONAL,
                    'for the file (If output is not set to json), the options include PNG, JPG, WebP, and PDF. The default is PNG'
                ),
                new InputOption(
                    'fail_on_error',
                    'e',
                    InputOption::VALUE_OPTIONAL,
                    'If fail on error is set to true, then the API will return an error if the render encounters a 4xx or 5xx status code. Default is false'
                ),
                new InputOption(
                    'scroll_to_element',
                    'm',
                    InputOption::VALUE_OPTIONAL,
                    'Only include expenses matching the specified status'
                ),
                new InputOption(
                    'selector',
                    's',
                    InputOption::VALUE_OPTIONAL,
                    'Only include expenses matching the specified status'
                ),
                new InputOption(
                    'full_page',
                    'fp',
                    InputOption::VALUE_OPTIONAL,
                    'Capture the full page of a website vs. the scrollable area that is visible in the viewport upon render. Default is false'
                ),
                new InputOption(
                    'lazy_load',
                    'l',
                    InputOption::VALUE_OPTIONAL,
                    'If lazy load is set to true, the browser will cross down the entire page to ensure all content is loaded in the render. Default is false'
                ),
                new InputOption(
                    'width',
                    'W',
                    InputOption::VALUE_OPTIONAL,
                    'Only include expenses matching the specified status'
                ),
                new InputOption(
                    'height',
                    'H',
                    InputOption::VALUE_OPTIONAL,
                    'Only include expenses matching the specified status'
                ),
            ]
        );
    }



}