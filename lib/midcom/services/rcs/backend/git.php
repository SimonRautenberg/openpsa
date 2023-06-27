<?php
/**
 * @package midcom.services.rcs
 * @author CONTENT CONTROL https://contentcontrol.berlin
 * @copyright CONTENT CONTROL https://contentcontrol.berlin
 */

/**
 * @package midcom.services.rcs
 */
class midcom_services_rcs_backend_git extends midcom_services_rcs_backend
{
    /**
     * Save a new revision
     */
    public function update(string $user_id, string $updatemessage = '')
    {
        $filename = $this->generate_filename();
        $relative_path = $this->relative_path($filename);
        $author = $user_id . ' <' . $user_id . '@' . $_SERVER['REMOTE_ADDR'] . '>';
        $command = 'commit -q --allow-empty --allow-empty-message -m ' . escapeshellarg($updatemessage) .
            ' --author ' . escapeshellarg($author) . ' ' . $relative_path;

        $this->write_object($filename);
        // avoid the separate add cmd where possible to mitigate concurrency issues
        if (!$this->read_handle('ls-files ' . $filename)[0]) {
            $this->exec('add ' . $relative_path);
        }

        $this->exec($command);
    }

    /**
     * Get a revision
     */
    public function get_revision(string $revision) : array
    {
        $filename = $this->generate_filename();
        $lines = $this->read_handle('show ' . $revision . ':' . $this->relative_path($filename));
        $mapper = new midcom_helper_exporter_xml();
        return $mapper->data2array(implode("\n", $lines));
    }

    protected function load_history() : array
    {
        $filename = $this->generate_filename();
        if (!is_readable($filename)) {
            debug_add('file ' . $filename . ' is not readable, returning empty result', MIDCOM_LOG_INFO);
            return [];
        }

        $lines = $this->read_handle('log --shortstat --format=format:"%h%n%ae%n%at%n%s" ' . $this->relative_path($filename));
        $total = count($lines);
        $revisions = [];

        for ($i = 0; $i < $total; $i += 6) {
            [$user, $ip] = explode('@', $lines[$i + 1], 2);
            $stat = preg_replace('/.*?\d file changed/', '', $lines[$i + 4]);
            $stat = preg_replace('/, (\d+) .+?tions?\(([\+\-])\)/', '$2$1 ', $stat);

            $revisions[$lines[$i]] = [
                'revision' => $lines[$i],
                'date' => $lines[$i + 2],
                'lines' => $stat,
                'user' => $user,
                'ip' => $ip,
                'message' => $lines[$i + 3]
            ];
        }

        return $revisions;
    }

    protected function generate_filename() : string
    {
        $root = $this->config->get_rootdir();
        $initialized = true;
        if (!file_exists($root . '/.git')) {
            if ((count(scandir($root)) > 2)) {
                // This is probably an old rcs dir
                throw new midcom_error($root . ' is not empty. Run tools/rcs2git to convert');
            }
            $initialized = false;
        }
        $filename = parent::generate_filename();

        if (!$initialized) {
            $this->exec('init');
            $this->exec('config user.email "midcom.rcs@localhost"');
            $this->exec('config user.name "midcom.rcs"');
        }
        return $filename;
    }

    protected function read_handle(string $command) : array
    {
        return parent::read_handle($this->get_command_prefix() . ' ' . $command);
    }

    private function exec(string $command)
    {
        $this->run_command($this->get_command_prefix($command != 'init') . ' ' . $command);
    }

    private function get_command_prefix(bool $initialized = true) : string
    {
        $prefix = 'git -C ' . $this->config->get_rootdir();
        if ($initialized) {
            // These help for the nested repo case
            $prefix .= ' --git-dir=' . $this->config->get_rootdir() . '/.git ' . ' --work-tree=' . $this->config->get_rootdir();
        }
        return $prefix;
    }

    private function relative_path(string $filename) : string
    {
        $relative_path = substr($filename, strlen($this->config->get_rootdir()));
        return escapeshellarg(trim($relative_path, '/'));
    }
}
