# Description

This npq module is a fork of the module of the same name created by Daniel Sipos (https://github.com/upchuk/d8-demo-modules).
It has been updated and adjusted to work with Drupal 9. "NPQ" is an initialism that stands for
"Node Processing Queue."

# Purpose

The puprose of this module is to demonstrate how to set up and use the Queue API
in Drupal. It provides two options: creating a queue that is processed by cron, and creating a queue
that is processed manually via a form submission. It's somewhat contrived purpose is to create a queue of
nodes that are unpublished. The queue can then be processed to publish those nodes. It isn't a super
useful function, but it serves to illustrate how the Queue API works.

# For more information

For more information, see the blog post Daniel Sipos wrote about it:
https://www.sitepoint.com/drupal-8-queue-api-powerful-manual-and-cron-queueing/.
Some of the content of the post is outdated, since it was written for Drupal 8, but it still
contains useful information.