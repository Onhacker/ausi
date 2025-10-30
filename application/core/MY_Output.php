<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class MY_Output extends CI_Output
{
    public function _display($output = '')
    {
        // Minify otomatis hanya untuk HTML
        if ($output == '') {
            $output = $this->final_output;
        }

        // Cek konten HTML
        if ($this->get_content_type() === 'text/html') {
            // Minify semua <script> dan <style> inline
            $output = $this->minify_html($output);
        }

        parent::_display($output);
    }

   private function minify_html($html)
{
    $pattern = '#(?>[^<]+|<(?!/?(?:script|style)\b)[^<]*)+|<script\b[^>]*>.*?</script>|<style\b[^>]*>.*?</style>#is';
    preg_match_all($pattern, $html, $matches);
    $result = '';
    foreach ($matches[0] as $part) {
        if (preg_match('#^<script|^<style#i', $part)) {
            $result .= $part; // Jangan ubah isi <script> dan <style>
        } else {
            $minified = preg_replace([
                '/\s{2,}/',
                '/\n|\r|\t/',
                '/>\s+</'
            ], [' ', '', '><'], $part);
            $result .= $minified;
        }
    }
    return $result;
}

}
