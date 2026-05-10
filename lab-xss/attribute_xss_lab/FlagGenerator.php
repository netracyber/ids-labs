<?php
/**
 * Flag Generator Class for XSS Labs
 * Integrates with the Python flag generator
 */

class FlagGenerator {
    private $flagFile;
    private $pythonScript;

    public function __construct($flagFile = '/tmp/attribute_flag.txt', $pythonScript = null) {
        $this->flagFile = $flagFile;
        // Use local tools directory if available, otherwise use app tools
        $localScript = __DIR__ . '/tools/generate_flag.py';
        $this->pythonScript = $pythonScript ?? (file_exists($localScript) ? $localScript : '/app/tools/generate_flag.py');
    }

    /**
     * Generate or retrieve existing flag
     */
    public function generate_flag() {
        // Check if flag already exists
        if (file_exists($this->flagFile)) {
            $flag = trim(file_get_contents($this->flagFile));
            if (!empty($flag) && strpos($flag, 'IDS{') === 0) {
                return $flag;
            }
        }

        // Generate new flag using Python script
        $flag = $this->run_python_generator();

        // Save to file
        file_put_contents($this->flagFile, $flag);

        return $flag;
    }

    /**
     * Run Python flag generator script
     */
    private function run_python_generator() {
        $output = shell_exec("python3 {$this->pythonScript} 2>&1");

        // Extract flag from output
        if (preg_match('/IDS\{[a-f0-9]{32}\}/', $output, $matches)) {
            return $matches[0];
        }

        // Fallback: generate flag directly in PHP
        return $this->generate_fallback_flag();
    }

    /**
     * Fallback flag generation if Python script fails
     */
    private function generate_fallback_flag() {
        $characters = '0123456789abcdef';
        $random_code = '';
        for ($i = 0; $i < 32; $i++) {
            $random_code .= $characters[random_int(0, strlen($characters) - 1)];
        }
        return 'IDS{' . $random_code . '}';
    }
}
