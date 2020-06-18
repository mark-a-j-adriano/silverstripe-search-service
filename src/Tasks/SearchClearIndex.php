<?php


namespace SilverStripe\SearchService\Tasks;

use SilverStripe\Control\HTTPRequest;
use SilverStripe\Core\Environment;
use SilverStripe\Dev\BuildTask;
use SilverStripe\SearchService\Interfaces\BatchDocumentInterface;
use SilverStripe\SearchService\Interfaces\IndexingInterface;
use SilverStripe\SearchService\Jobs\ClearIndexJob;
use SilverStripe\SearchService\Service\Traits\BatchProcessorAware;
use SilverStripe\SearchService\Service\Traits\ConfigurationAware;
use SilverStripe\SearchService\Service\IndexConfiguration;
use SilverStripe\SearchService\Service\Traits\ServiceAware;
use SilverStripe\SearchService\Service\SyncJobRunner;

class SearchClearIndex extends BuildTask
{
    use ServiceAware;
    use ConfigurationAware;
    use BatchProcessorAware;

    protected $title = 'Search Service Clear Index';

    protected $description = 'Search Service Clear Index';

    private static $segment = 'SearchClearIndex';

    /**
     * @var BatchDocumentInterface
     */
    private $batchProcessor;

    /**
     * SearchReindex constructor.
     * @param IndexingInterface $searchService
     * @param IndexConfiguration $config
     * @param BatchDocumentInterface $batchProcesor
     */
    public function __construct(
        IndexingInterface $searchService,
        IndexConfiguration $config,
        BatchDocumentInterface $batchProcesor
    ) {
        parent::__construct();
        $this->setIndexService($searchService);
        $this->setConfiguration($config);
        $this->setBatchProcessor($batchProcesor);
    }

    /**
     * @param HTTPRequest $request
     */
    public function run($request)
    {
        Environment::increaseMemoryLimitTo();
        Environment::increaseTimeLimitTo();

        $targetIndex = $request->getVar('index');
        if (!$targetIndex) {
            echo "Must specify an index in the 'index' parameter." . PHP_EOL;
            die();
        }
        $job = ClearIndexJob::create($targetIndex);
        SyncJobRunner::singleton()->runJob($job);
    }
}
