---
title: 'Sphinx search performance optimization: multi-threaded search'
source:
  name: Percona Blog
  url: https://www.percona.com/blog/sphinx-search-performance-optimization-multi-threaded-search/
  post_id: 6534
source_author:
  name: Aurimas Mikalauskas
  slug: inner
  url: https://www.percona.com/blog/author/inner/
  website: http://www.speedemy.com/
published_at: '2013-01-16T13:27:09'
published_at_gmt: '2013-01-16T13:27:09'
modified_at: '2026-04-28T21:48:52'
modified_at_gmt: '2026-04-28T21:48:52'
curator:
  name: Sylvain ARBAUDIE
  slug: sylvain-arbaudie
  source_url: https://www.pmacontrol.fr/fr/site/blog_author/sylvain-arbaudie/
matched_topics:
- MySQL
matched_filters:
- category:mysql:83
categories:
- MySQL
category_slugs:
- mysql
tags:
- FullText Search
- Performance
- Sphinx
- Tips
tag_slugs:
- fulltext-search
- performance
- sphinx
- tips
featured_image_url: ''
image_count: 0
graph_or_chart_count: 0
content_import: metadata_excerpt_headings_and_source_image_urls_only
copyright_notice: Percona All Rights Reserved; full article text and local image copies are not reproduced.
---

# Sphinx search performance optimization: multi-threaded search

Source: [Percona Blog](https://www.percona.com/blog/sphinx-search-performance-optimization-multi-threaded-search/)

Auteur source: [Aurimas Mikalauskas](https://www.percona.com/blog/author/inner/)

Publication: 2013-01-16T13:27:09

Curateur PmaControl: [Sylvain ARBAUDIE](../../../authors/sylvain-arbaudie.md)

## Note de droits

Fiche de veille uniquement: les metadonnees, l'extrait public, les titres de sections detectes et les URLs des visuels sont conserves. Le texte complet et les copies locales des images restent sur la source Percona.

## Extrait public

Queries in MySQL, Sphinx and many other database or search engines are typically single-threaded. That is when you issue a single query on your brand new r910 with 32 CPU cores and 16 disks, the maximum that is going to be used to process this query at any given point is 1 CPU core and … Continued

## Structure detectee

- H2: The Plan
- H2: Execution
- H2: Finishing line

## Auteur source

Aurimas joined Percona as a first MySQL Performance Consultant in 2006, a few months after Peter and Vadim founded the company. His primary focus is on high performance, but he also specializes in full text search, high availability, content caching techniques and MySQL data recovery.
