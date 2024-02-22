---
title: Console commands
---
# Console commands

Scout provides two easy console commands for managing your indices.

### Importing
To import one or all indices you can run the following console command

```
./craft scout/index/import <indexName?>
```

The `indexName` argument is not required, all your mappings will be imported when you omit it.

### Flushing/Clearing
Clearing an index is as easy as running a command in your console.

```
./craft scout/index/flush <indexName?>
```

As with the import command, `indexName` is not required.

When flushing, Scout will ask you to confirm that you really want to clear all the data in your index. You can bypass the confirmation by appending a `--force` flag.

### Refreshing
Does a flush/clear first and then imports the index again.

```
./craft scout/index/refresh <indexName?>
```