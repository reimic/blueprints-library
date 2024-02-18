<?php


namespace WordPress\Blueprints\Runner\Step;

use WordPress\Blueprints\Model\DataClass\ImportFileStep;
use WordPress\Blueprints\Progress\Tracker;


class ImportFileStepRunner extends BaseStepRunner {

	function run( ImportFileStep $input, Tracker $tracker ) {
		$tracker?->setCaption( $input->progress->caption ?? "Importing starter content" );

		// @TODO: Install the wordpress-importer plugin if it's not already installed
		//        wp plugin install wordpress-importer --activate
		//        Perhaps we'll need to package up some of these tasks in separate classes
		//        to make them more reusable? Or should we just reuse the existing steps?

		return $this->resourceManager->bufferToTemporaryFile(
			$input->file,
			function ( $path ) use ( $input ) {
				return $this->getRuntime()->runShellCommand(
					[
						'php',
						'wp-cli.phar',
						'import',
						$path,
						'--authors=create',
					]
				);
			},
			'.wxr'
		);
	}
}
