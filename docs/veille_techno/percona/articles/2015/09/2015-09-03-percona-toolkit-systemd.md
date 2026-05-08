---
title: Percona Toolkit and systemd
source:
  name: Percona Blog
  url: https://www.percona.com/blog/percona-toolkit-systemd/
  post_id: 9928
source_author:
  name: Andrew Moore
  slug: amoore
  url: https://www.percona.com/blog/author/amoore/
  website: ''
published_at: '2015-09-03T15:18:33'
published_at_gmt: '2015-09-03T15:18:33'
modified_at: '2026-05-05T22:40:55'
modified_at_gmt: '2026-05-05T22:40:55'
curator:
  name: Sylvain ARBAUDIE
  slug: sylvain-arbaudie
  source_url: https://www.pmacontrol.fr/fr/site/blog_author/sylvain-arbaudie/
matched_topics:
- MySQL
- PMM
- Percona Toolkit
matched_filters:
- category:mysql:83
- search:percona-monitoring-and-management
- search:percona-toolkit
- tag:percona-toolkit:378
categories:
- MySQL
category_slugs:
- mysql
tags:
- Andrew Moore
- Lennart Poettering
- MySQL
- Percona Toolkit
- pt-kill
- pt-stalk
- systemd
tag_slugs:
- andrew-moore
- lennart-poettering
- mysql
- percona-toolkit
- pt-kill
- pt-stalk
- systemd
featured_image_url: ''
image_count: 0
graph_or_chart_count: 0
content_import: metadata_excerpt_headings_and_source_image_urls_only
copyright_notice: Percona All Rights Reserved; full article text and local image copies are not reproduced.
---

# Percona Toolkit and systemd

Source: [Percona Blog](https://www.percona.com/blog/percona-toolkit-systemd/)

Auteur source: [Andrew Moore](https://www.percona.com/blog/author/amoore/)

Publication: 2015-09-03T15:18:33

Curateur PmaControl: [Sylvain ARBAUDIE](../../../authors/sylvain-arbaudie.md)

## Note de droits

Fiche de veille uniquement: les metadonnees, l'extrait public, les titres de sections detectes et les URLs des visuels sont conserves. Le texte complet et les copies locales des images restent sur la source Percona.

## Extrait public

After some recent work with systemd I’ve realized it’s power and I can come clean that I am a fan. I realize that there are multitudes of posts out there with arguments both for and against systemd but let’s look at some nice ways to have systemd provide us with (but not limited to) pt-kill-as-a-service. … Continued

## Structure detectee

- H3: Systemd what? When did this happen?
- H3: Systemd and Percona Toolkit
- H3: The systemd Unit File
- H3: Start up & enable
- H3: Catch me if I fall
- H4: Closing note

## Auteur source

Since fall 2013, Andrew has been working within Percona's Remote DBA team plying his experience to the client's environments and internal tools developed to keep operations slick. He lives in the UK with his young family and loves to complain about the less than perfect climate. Andrew makes time to pursue an amateur soccer career but won't be trading in MySQL any time soon.
