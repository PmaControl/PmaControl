---
title: Knowing what pt-online-schema-change will do
source:
  name: Percona Blog
  url: https://www.percona.com/blog/knowing-what-pt-online-schema-change-will-do/
  post_id: 3587
source_author:
  name: Daniel Nichter
  slug: daniel
  url: https://www.percona.com/blog/author/daniel/
  website: http://www.percona.com
published_at: '2012-11-01T21:02:45'
published_at_gmt: '2012-11-01T21:02:45'
modified_at: '2026-05-04T21:46:02'
modified_at_gmt: '2026-05-04T21:46:02'
curator:
  name: Sylvain ARBAUDIE
  slug: sylvain-arbaudie
  source_url: https://www.pmacontrol.fr/fr/site/blog_author/sylvain-arbaudie/
matched_topics:
- Percona Toolkit
matched_filters:
- search:percona-toolkit
categories:
- Insight for DBAs
- Percona Software
category_slugs:
- insight-for-dbas
- percona-software
tags:
- pt-online-schema-change
tag_slugs:
- pt-online-schema-change
featured_image_url: ''
image_count: 0
graph_or_chart_count: 0
content_import: metadata_excerpt_headings_and_source_image_urls_only
copyright_notice: Percona All Rights Reserved; full article text and local image copies are not reproduced.
---

# Knowing what pt-online-schema-change will do

Source: [Percona Blog](https://www.percona.com/blog/knowing-what-pt-online-schema-change-will-do/)

Auteur source: [Daniel Nichter](https://www.percona.com/blog/author/daniel/)

Publication: 2012-11-01T21:02:45

Curateur PmaControl: [Sylvain ARBAUDIE](../../../authors/sylvain-arbaudie.md)

## Note de droits

Fiche de veille uniquement: les metadonnees, l'extrait public, les titres de sections detectes et les URLs des visuels sont conserves. Le texte complet et les copies locales des images restent sur la source Percona.

## Extrait public

pt-online-schema-change is simple to use, but internally it is complex. Baron’s webinar about pt-online-schema-change hinted at several of the tool’s complexities. Consequently, users often want to know before making changes what pt-online-schema-change will do when it runs. The tool has two options to help answer this question: –dry-run and –print. When ran with –dry-run and –print, pt-online-schema-change changes nothing … Continued
