---
title: 'Sphinx search performance optimization: attribute-based filters'
source:
  name: Percona Blog
  url: https://www.percona.com/blog/sphinx-search-performance-optimization-attribute-based-filtering/
  post_id: 6530
source_author:
  name: Aurimas Mikalauskas
  slug: inner
  url: https://www.percona.com/blog/author/inner/
  website: http://www.speedemy.com/
published_at: '2013-01-15T19:45:55'
published_at_gmt: '2013-01-15T19:45:55'
modified_at: '2026-05-04T21:55:44'
modified_at_gmt: '2026-05-04T21:55:44'
curator:
  name: Sylvain ARBAUDIE
  slug: sylvain-arbaudie
  source_url: https://www.pmacontrol.fr/fr/site/blog_author/sylvain-arbaudie/
matched_topics:
- MySQL
matched_filters:
- category:mysql:83
categories:
- Insight for Developers
- MySQL
category_slugs:
- insight-for-developers
- mysql
tags:
- FullText Search
- Sphinx
- Tips
tag_slugs:
- fulltext-search
- sphinx
- tips
featured_image_url: ''
image_count: 0
graph_or_chart_count: 0
content_import: metadata_excerpt_headings_and_source_image_urls_only
copyright_notice: Percona All Rights Reserved; full article text and local image copies are not reproduced.
---

# Sphinx search performance optimization: attribute-based filters

Source: [Percona Blog](https://www.percona.com/blog/sphinx-search-performance-optimization-attribute-based-filtering/)

Auteur source: [Aurimas Mikalauskas](https://www.percona.com/blog/author/inner/)

Publication: 2013-01-15T19:45:55

Curateur PmaControl: [Sylvain ARBAUDIE](../../../authors/sylvain-arbaudie.md)

## Note de droits

Fiche de veille uniquement: les metadonnees, l'extrait public, les titres de sections detectes et les URLs des visuels sont conserves. Le texte complet et les copies locales des images restent sur la source Percona.

## Extrait public

One of the most common causes of a poor Sphinx search performance I find our customers face is misuse of search filters. In this article I will cover how Sphinx attributes (which are normally used for filtering) work, when they are a good idea to use and what to do when they are not, but … Continued

## Structure detectee

- H2: The Problem
- H2: For example..
- H2: Solution
- H2: Highly selective columns only

## Auteur source

Aurimas joined Percona as a first MySQL Performance Consultant in 2006, a few months after Peter and Vadim founded the company. His primary focus is on high performance, but he also specializes in full text search, high availability, content caching techniques and MySQL data recovery.
