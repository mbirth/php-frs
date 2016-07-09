#export PATH := $(PWD)/vendor/bin:$(PATH)

CC=$(PWD)/vendor/bin/coffee
SCRIPT_DIR=js
COFFEE_FILES := $(wildcard $(SCRIPT_DIR)/*.coffee)
JS_FILES=$(COFFEE_FILES:$(SCRIPT_DIR)/%.coffee=$(SCRIPT_DIR)/%.js)

all: coffee

coffee: $(JS_FILES)

$(SCRIPT_DIR)/%.js: $(SCRIPT_DIR)/%.coffee
	$(CC) -c $<





# Cleanup

.PHONY: clean
clean:
	-rm $(SCRIPT_DIR)/*.js
