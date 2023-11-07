<?php

namespace App\Services\Facturacion\WS\Zip;

use Exception;

/**
 * Class ZipFileDecompress.
 */
class ZipFileDecompress
{
    /**
     * Extract files.
     *
     * @param string        $content
     * @param callable|null $filter
     *
     * @return array
     */
    public function decompress($content, callable $filter = null)
    {
        try {
            $temp = tempnam(sys_get_temp_dir(), time().'.zip');
            file_put_contents($temp, $content);
            $zip = new \ZipArchive();
            $output = [];
            if (true === $zip->open($temp) && $zip->numFiles > 0) {
                $output = iterator_to_array($this->getFiles($zip, $filter));
            }
            $zip->close();
            if (file_exists($temp)) { @unlink($temp); }
        } catch(Exception $e) {
            return false;
        }

        return $output;
    }

    private function getFiles(\ZipArchive $zip, $filter)
    {
        $total = $zip->numFiles;
        for ($i = 0; $i < $total; ++$i) {
            $name = $zip->getNameIndex($i);
            if (false === $name) {
                continue;
            }

            if (!$filter || $filter($name)) {
                yield [
                    'filename' => $name,
                    'content' => $zip->getFromIndex($i),
                ];
            }
        }
    }
}
