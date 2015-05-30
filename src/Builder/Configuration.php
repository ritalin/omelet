<?php

namespace Omelet\Builder;

final class Configuration {
	/**
	 * @var string
	 */
    public $daoClassPath;
    /**
     * @var string
     */
    public $sqlRootDir;
    /**
     * @var string
     */
    public $daoClassSuffix = 'Impl';

    /**
     * @var string
     */
    public $connectionString = '';

    /**
     * @var string
     */
    public $watchMode = 'Whenever';
    /**
     * @var string
     */
    public $paramCaseSensor = 'LowerSnake';
    /**
     * @var string
     */
    public $returnCaseSensor = 'LowerSnake';

    private static $requiredMsg = ' is required.';
    private static $oneOfMsg = ' is one of %s';

    /**
     * @param callable fn
     *
     * $fn => Configuration -> Void
     */
    public function __construct(callable $fn = null) {
        $appDir = dirname(dirname(__DIR__));
        $this->daoClassPath = $appDir . '/sql';
        $this->sqlRootDir = $appDir . '/_auto_generated';

    	if ($fn !== null) {
    		$fn($this);
        }
    }

    /**
     * @param boolean nothrow
     */
    public function validate($noThrow = false) {
    	$invalidFields = [];

    	if (! $this->required($this->daoClassPath)) $invalidFields['daoClassPath'] = self::$requiredMsg;
    	if (! $this->required($this->sqlRootDir)) $invalidFields['sqlRootDir'] = self::$requiredMsg;
    	if (! $this->required($this->daoClassSuffix)) $invalidFields['daoClassSuffix'] = self::$requiredMsg;
    	if (! $this->required($this->connectionString)) $invalidFields['connectionString'] = self::$requiredMsg;

    	$availables = ['Always', 'Whenever', 'Once'];
    	if (! $this->contains($this->watchMode, $availables)) {
    		$invalidFields['watchMode'] = sprintf(self::$oneOfMsg, implode(', ', $availables));
    	}

    	$availables = ['LowerCamel', 'UpperCamel', 'LowerSnake', 'UpperSnake'];
    	if (! $this->contains($this->paramCaseSensor, $availables)) {
    		$invalidFields['paramCaseSensor'] = sprintf(self::$oneOfMsg, implode(', ', $availables));
    	}
    	if (! $this->contains($this->returnCaseSensor, $availables)) {
    		$invalidFields['returnCaseSensor'] = sprintf(self::$oneOfMsg, implode(', ', $availables));
    	}

    	if ($noThrow) {
    		return $invalidFields;
    	}
    	else if ($invalidFields) {
    		$messages = [];
    		foreach ($invalidFields as $f => $msg) {
    			$messages[] = $f . $msg;
    		}

    		throw new \RuntimeException(implode("\n", $messages));
    	}
    }

    private function required($val) {
    	return isset($val) && $val !== '';
    }
    private function contains($val, &$availables) {
    	return in_array($val, $availables);
    }
}