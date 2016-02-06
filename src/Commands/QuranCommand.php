<?php

namespace FaizShukri\Quran\Commands;

use FaizShukri\Quran\Quran;
use Hoa\Console\Parser;
use Hoa\Console\GetOption;

class QuranCommand
{
    private $uri;

    private $options;

    private $parser;

    public function __construct($uri)
    {
        $this->uri = $uri;
        $this->parser = new Parser();
        $this->parser->parse($this->uri);
        $this->options = new GetOption(
            [
                ['help',    GetOption::NO_ARGUMENT, 'h'],
                ['version', GetOption::NO_ARGUMENT, 'v']
            ],
            $this->parser
        );
    }

    public function execute()
    {
        $this->processFlag();

        if ($this->options->isPipetteEmpty()) {
            $this->processAyah();
        }
    }

    public function processFlag()
    {
        while (false !== $c = $this->options->getOption($v)) {
            switch ($c) {
                case '__ambiguous':
                    echo "Option `" . $v['option'] . "` does not exists.";

                    if (sizeof($v['solutions']) > 0) {
                        echo "Do you mean one of below?\n";
                        echo implode("\n", array_map(function ($opt) {
                            return " - $opt";
                        }, $v['solutions']));
                    }
                    break;

                case 'h':
                    echo $this->usage();
                    break;
                case 'v':
                    echo "Quran-Cli v3.2.2 by Faiz Shukri";
            }
            echo "\n\n";
        }
    }

    public function processAyah()
    {
        $this->parser->listInputs($ayah, $translation);
        $quran = new Quran();

        try {
            if ( $translation ) {
                $quran->translation( $translation );
            }

            $ayah = $quran->get($ayah);
            echo $this->parseResult($ayah) . "\n";

        } catch (\Exception $e) {
            return "Error: " . $e->getMessage() . "\n\n";
        }
    }

    public function usage()
    {
        return
            "Usage:" . "\n" .
            "  quran <surah> <ayah> [<translation>='ar'] <options>" . "\n\n" .

            "Example: " . "\n" .
            "  quran 3:6               (Surah 3, ayah 6)". "\n".
            "  quran 3:2,4-6 ar,en     (Surah 3, ayah 2,4,5,6, in arabic and english)". "\n\n" .

            "Options:" . "\n" .
            "  -h, --help    : This help" . "\n" .
            "  -v, --version : Show version";
    }



    private function parseResult($args)
    {
        // Just a single ayah is return. No need to parse anything.
        if (is_string($args)) {
            return $args . "\n";
        }

        // Multiple ayah/one surah or multiple surah/one ayah. Not both.
        if (is_string(current($args))) {
            return $this->buildAyah($args);
        }

        // Both multiple ayah and multiple surah.
        $count = 0;
        $result = "\n";

        foreach ($args as $translation => $aya) {

            $result .= strtoupper($translation) . "\n" . str_repeat('=', strlen($translation) + 2) . "\n\n";
            $result .= $this->buildAyah($aya);

            ++$count;
            if ($count < sizeof($args)) {
                $result .= "\n\n";
            }
        }

        return $result;

    }

    private function buildAyah($ayah)
    {
        $result = "";
        foreach ($ayah as $num => $aya) {
            $result .= "[ " . strtoupper($num) . " ]\t" . $aya . "\n";
        }
        return $result;
    }
}