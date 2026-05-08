---
title: How to Manually Build Percona Server for MySQL RPM Packages
source:
  name: Percona Blog
  url: https://www.percona.com/blog/how-to-manually-build-percona-server-rpm-packages/
  post_id: 16152
source_author:
  name: Evgeniy Patlan
  slug: evgeniy-patlan
  url: https://www.percona.com/blog/author/evgeniy-patlan/
  website: ''
published_at: '2017-01-20T17:58:07'
published_at_gmt: '2017-01-20T17:58:07'
modified_at: '2026-05-05T20:14:12'
modified_at_gmt: '2026-05-05T20:14:12'
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
- custom build
- GitHub
- MySQL
- RPM
- tarball
tag_slugs:
- custom-build
- github
- mysql
- rpm
- tarball
featured_image_url: https://www.percona.com/wp-content/uploads/2026/03/RPM-Packages.jpg
image_count: 1
graph_or_chart_count: 0
content_import: metadata_excerpt_headings_and_source_image_urls_only
copyright_notice: Percona All Rights Reserved; full article text and local image copies are not reproduced.
---

# How to Manually Build Percona Server for MySQL RPM Packages

Source: [Percona Blog](https://www.percona.com/blog/how-to-manually-build-percona-server-rpm-packages/)

Auteur source: [Evgeniy Patlan](https://www.percona.com/blog/author/evgeniy-patlan/)

Publication: 2017-01-20T17:58:07

Curateur PmaControl: [Sylvain ARBAUDIE](../../../authors/sylvain-arbaudie.md)

## Note de droits

Fiche de veille uniquement: les metadonnees, l'extrait public, les titres de sections detectes et les URLs des visuels sont conserves. Le texte complet et les copies locales des images restent sur la source Percona.

## Extrait public

In this blog, we’ll look at how to manually build Percona Server for MySQL RPM packages. Several customers and other people from the open source community have asked us how they could make their own Percona Server for MySQL RPM binaries from scratch. This request is often made by companies that want to add custom … Continued

## Structure detectee

- H2: Prepare the Source
- H3: Using GIT Repository
- H3: Downloading Source Tarball
- H2: Making Changes with Patch Files
- H3: Why Patches?
- H3: Create Patch Files
- H3: Add Patch to RPM Spec File
- H2: Preparing Build Environment
- H3: Environment Requirements
- H3: Install Dependencies
- H3: Prepare RPM Build Tree
- H3: Download Boost Source
- H3: Move Files to the RPM Build Tree
- H2: Setting Correct Versions in the Spec File
- H2: Building RPM Packages

## Images et graphiques reperes

- featured / image: [How to Manually Build Percona Server for MySQL RPM Packages](https://www.percona.com/wp-content/uploads/2026/03/RPM-Packages.jpg)

## Auteur source

Evgeniy joined Percona in April 2016. He is working as a Lead Release Engineer. Previously he was working for more than 8 years for PortaOne Inc. where he changed roles from support engineer to build/release engineer.
