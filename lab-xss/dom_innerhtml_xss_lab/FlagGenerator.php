<?php
/**
 * Flag Generator Class for XSS Labs
 */

class FlagGenerator {
    private $flagFile;
    private $pythonScript;

    public function __construct($flagFile = '/tmp/current_flag.txt', $pythonScript = null) {
        $this->flagFile = $flagFile;
        $localScript = __DIR__ . '/tools/generate_flag.py';
        $this->pythonScript = $pythonScript ?? (file_exists($localScript) ? $localScript : '/usr/local/bin/generate_flag.py');
    }

    public function generate_flag() {
        if (file_exists($this->flagFile)) {
            $flag = trim(file_get_contents($this->flagFile));
            if (!empty($flag) && strpos($flag, 'IDS{') === 0) {
                return $flag;
            }
        }

        $flag = $this->run_python_generator();
        file_put_contents($this->flagFile, $flag);
        return $flag;
    }

    private function run_python_generator() {
        $output = shell_exec("python3 {$this->pythonScript} 2>&1");

        if (preg_match('/IDS\{[A-Za-z0-9]+\}/', $output, $matches)) {
            return $matches[0];
        }

        return $this->generate_fallback_flag();
    }

    private function generate_fallback_flag() {
        $characters = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789';
        $random_code = '';
        for ($i = 0; $i < 20; $i++) {
            $random_code .= $characters[random_int(0, strlen($characters) - 1)];
        }
        return 'IDS{' . $random_code . '}';
    }
}
