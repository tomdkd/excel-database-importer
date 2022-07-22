.DEFAULT_GOAL := help

.PHONY: help
help:
	@echo  "$$(grep -hE '^\S+:.*##' $(MAKEFILE_LIST) | sort | sed -e 's/:.*##\s*/:/' -e 's/^\(.\+\):\(.*\)/\x1b[36m\1\x1b[m:\2/' | column -c2 -t -s :)"

.PHONY: update-dep
update-dep: ## Update composer dependencies
	composer update

.PHONY: cs
cs: ## Check code with sniffer
	./vendor/bin/phpcs ./src

.PHONY: fix-cs
fix-cs: ## Fix code sniffer errors
	php ./vendor/bin/phpcbf ./src