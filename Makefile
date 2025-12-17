PHP_CODESNIFFER_ARGS = -p
PHP_COMMAND=php
COMPOSER_COMMAND=$(PHP_COMMAND) /usr/local/bin/composer

.PHONY: lint
lint: phpcs-report psalm-no-cache

.PHONY: psalm-no-cache
psalm-no-cache:
	$(PHP_COMMAND) ./vendor/bin/psalm --show-info=false --no-cache

.PHONY: phpcs-report
phpcs-report:
	$(PHP_COMMAND) ./vendor/bin/phpcs $(PHP_CODESNIFFER_ARGS)

.PHONY: unit-tests
unit-tests: composer-install lint unit-tests-only

.PHONY: unit-tests-only
unit-tests-only:
	$(PHP_COMMAND) ./vendor/bin/phpunit

.PHONY: unit-tests-only-debug
unit-tests-only-debug:
	$(PHP_COMMAND) ./vendor/bin/phpunit --debug

.PHONY: composer-install
composer-install:
	$(COMPOSER_COMMAND) install

.PHONY: cmpinst
cmpinst: composer-install
