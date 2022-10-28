<?php
/**
 * @package midcom.console
 * @author CONTENT CONTROL http://www.contentcontrol-berlin.de/
 * @copyright CONTENT CONTROL http://www.contentcontrol-berlin.de/
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License
 */

namespace midcom\console;

use Symfony\Component\Console\Application as base_application;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use midcom;

/**
 * OpenPSA CLI command runner
 *
 * @package midcom.console
 */
class application extends base_application
{
    /**
     * @inheritDoc
     */
    public function __construct($name = 'midcom\console', $version = midcom::VERSION)
    {
        parent::__construct($name, $version);

        $this->getDefinition()
            ->addOption(new InputOption('--servername', '-s', InputOption::VALUE_REQUIRED, 'HTTP server name', 'localhost'));
        $this->getDefinition()
            ->addOption(new InputOption('--port', '-p', InputOption::VALUE_REQUIRED, 'HTTP server port', '80'));

        $this->add(new command\exec);
        $this->add(new command\purgedeleted);
        $this->add(new command\repligard);
        $this->add(new command\cleanup\blobdir);
        $this->add(new command\cleanup\parameters);
        $this->add(new command\cleanup\privileges);
        $this->add(new command\cleanup\rcsdir);
        $this->add(new command\reindex);
        $this->add(new command\cron);
        $this->add(new command\cacheinvalidate);
    }

    /**
     * @inheritDoc
     */
    public function doRun(InputInterface $input, OutputInterface $output)
    {
        $GLOBALS['midcom_config_local']['cache_module_content_uncached'] = true;

        $port = $input->getParameterOption(['--port', '-p'], '80');
        $servername = $input->getParameterOption(['--servername', '-s'], md5(__FILE__));

        $server_defaults = [
            'HTTP_HOST' => $servername,
            'SERVER_NAME' => $servername,
            'SERVER_SOFTWARE' => __CLASS__,
            'HTTP_USER_AGENT' => $this->getName(),
            'SERVER_PORT' => $port,
            'REMOTE_ADDR' => '127.0.0.1',
            'REQUEST_URI' => '/',
            'REQUEST_TIME' => time(),
            'REMOTE_PORT' => $port
        ];
        $_SERVER = array_merge($server_defaults, $_SERVER);

        if ($_SERVER['SERVER_PORT'] == 443) {
            $_SERVER['HTTPS'] = 'on';
        }

        // This makes sure that existing auth and cache instances get overridden
        midcom::init();

        parent::doRun($input, $output);
    }
}
