<?php

namespace WordPress\Blueprints\Runner\Step;

use WordPress\Blueprints\Context\ExecutionContext;
use WordPress\Blueprints\Resource\ResourceManager;
use WordPress\Blueprints\Runtime\RuntimeInterface;

abstract class BaseStepRunner implements StepRunnerInterface {
	protected ResourceManager $resourceManager;

	protected RuntimeInterface $runtime;

	public function setResourceManager( ResourceManager $map ) {
		$this->resourceManager = $map;
	}

	protected function getResource( $declaration ) {
		return $this->resourceManager->getStream( $declaration );
	}

	public function setRuntime( RuntimeInterface $runtime ): void {
		$this->runtime = $runtime;
	}

	protected function getRuntime(): RuntimeInterface {
		return $this->runtime;
	}

	public function getDefaultCaption( $input ): string|null {
		return null;
	}

}
