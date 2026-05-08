---
title: 'Can’t Create a New Thread: Errno 11 Fixes'
source:
  name: Percona Blog
  url: https://www.percona.com/blog/cant_create_thread_errno_11/
  post_id: 6538
source_author:
  name: Ernie Souhrada
  slug: percona_ews
  url: https://www.percona.com/blog/author/percona_ews/
  website: http://www.percona.com
published_at: '2013-02-04T14:43:01'
published_at_gmt: '2013-02-04T14:43:01'
modified_at: '2026-05-04T21:56:37'
modified_at_gmt: '2026-05-04T21:56:37'
curator:
  name: Sylvain ARBAUDIE
  slug: sylvain-arbaudie
  source_url: https://www.pmacontrol.fr/fr/site/blog_author/sylvain-arbaudie/
matched_topics:
- MySQL
matched_filters:
- category:mysql:83
categories:
- Insight for DBAs
- MySQL
category_slugs:
- insight-for-dbas
- mysql
tags:
- 90-nproc.conf
- Errno 11
- Ulimits
tag_slugs:
- 90-nproc-conf
- errno-11
- ulimits
featured_image_url: ''
image_count: 0
graph_or_chart_count: 0
content_import: metadata_excerpt_headings_and_source_image_urls_only
copyright_notice: Percona All Rights Reserved; full article text and local image copies are not reproduced.
---

# Can’t Create a New Thread: Errno 11 Fixes

Source: [Percona Blog](https://www.percona.com/blog/cant_create_thread_errno_11/)

Auteur source: [Ernie Souhrada](https://www.percona.com/blog/author/percona_ews/)

Publication: 2013-02-04T14:43:01

Curateur PmaControl: [Sylvain ARBAUDIE](../../../authors/sylvain-arbaudie.md)

## Note de droits

Fiche de veille uniquement: les metadonnees, l'extrait public, les titres de sections detectes et les URLs des visuels sont conserves. Le texte complet et les copies locales des images restent sur la source Percona.

## Extrait public

Recently some of my fellow Perconians and I have noticed a bit of an uptick in customer cases featuring the following error message: SQLSTATE[HY000] [1135] Can't create a new thread (errno 11); if you are not out of available memory, you can consult the manual for a possible OS-dependent bug. 1 2 SQLSTATE [ HY000 ] [ 1135 ] Can ' t create a new thread ( errno 11 ) ; if you are not out of available memory , you can consult the manual for a possible OS - dependent bug . The canonical solution to this issue, if you do a bit of Googling, is to increase the number of processes / threads available to the MySQL user, typically by adding a … Continued

## Structure detectee

- H2: Fedora 9 and RHEL 6
- H2: Increase Process Limit Under Fedora 9 and RHEL 6
- H2: The Problem with Adjusting limits.conf and 90-nproc.conf
- H2: The Errno 11 Fix for Fedora 9 and RHEL 6

## Auteur source

Ernie joined Percona in April 2012 as a Senior Consultant. In his previous lives, he has been everything from a Perl/Java developer to a Linux sysadmin, a MySQL DBA to a Cisco network engineer, and a security auditor to an IT engineering manager, many of these things all at the same time. When not working on MySQL, he might be found on the ski slope, at a psytrance festival, or at the nearest sushi bar.
