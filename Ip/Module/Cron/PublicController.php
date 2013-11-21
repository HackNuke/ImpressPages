<?php
/**
 * @package ImpressPages
 *
 */

namespace Ip\Module\Cron;



class PublicController extends \Ip\Controller
{
    /** true if cron is executed first time this year
     * @var bool */
    protected $firstTimeThisYear;
    /** true if cron is executed first time this month
     * @var bool */
    protected $firstTimeThisMonth;
    /** true if cron is executed first time this week
     * @var bool */
    protected $firstTimeThisWeek;
    /** true if cron is executed first time this day
     * @var bool */
    protected $firstTimeThisDay;
    /** true if cron is executed first time this hour
     * @var bool */
    protected $firstTimeThisHour;
    /** last cron execution time
     * @var string */
    protected $lastTime;

    public function index()
    {
        if (ipRequest()->getRequest('pass', '') != ipGetOption('Config.cronPassword')) {
            ipLog()->notice('Incorrect cron password from ip `{ip}`.', array('plugin' => 'Cron', 'ip' => ipRequest()->getServer('REMOTE_ADDR')));
        }

        \Ip\ServiceLocator::storage()->set('Cron', 'lastExecutionStart', time());
        ipLog()->info('Cron started.', array('plugin' => 'Cron'));
        $data = array(
            'firstTimeThisYear' => $this->firstTimeThisYear,
            'firstTimeThisMonth' => $this->firstTimeThisMonth,
            'firstTimeThisWeek' => $this->firstTimeThisWeek,
            'firstTimeThisDay' => $this->firstTimeThisDay,
            'firstTimeThisHour' => $this->firstTimeThisHour,
            'lastTime' => $this->lastTime,
            'test' => ipRequest()->getQuery('test')
        );

        ipDispatcher()->notify('Cron.execute', $data);

        \Ip\ServiceLocator::storage()->set('Cron', 'lastExecutionEnd', time());
        ipLog()->info('Cron finished.', array('plugin' => 'Cron'));

        $response = new \Ip\Response();
        $response->setContent(__('OK', 'ipAdmin'));
        return $response;
    }

    public function init()
    {
        $this->firstTimeThisYear = true;
        $this->firstTimeThisMonth = true;
        $this->firstTimeThisWeek = true;
        $this->firstTimeThisDay = true;
        $this->firstTimeThisHour = true;
        $this->lastTime = null;

        $lastExecution = \Ip\ServiceLocator::storage()->get('Cron', 'lastExecutionEnd', NULL);
        $lastExecutionStart = \Ip\ServiceLocator::storage()->get('Cron', 'lastExecutionStart', NULL);
        if ($lastExecution < $lastExecutionStart) { // if last cron execution failed to finish
            $lastExecution = $lastExecutionStart;
        }

        if (!$lastExecution && !(ipRequest()->getQuery('test') && isset($_SESSION['backend_session']['userId']))) {
            $this->firstTimeThisYear = date('Y') != date('Y', $lastExecution);
            $this->firstTimeThisMonth = date('Y-m') != date('Y-m', $lastExecution);
            $this->firstTimeThisWeek = date('Y-w') != date('Y-w', $lastExecution);
            $this->firstTimeThisDay = date('Y-m-d') != date('Y-m-d', $lastExecution);
            $this->firstTimeThisHour = date('Y-m-d H') != date('Y-m-d H', $lastExecution);
            $this->lastTime = $lastExecution;
        }
    }


}