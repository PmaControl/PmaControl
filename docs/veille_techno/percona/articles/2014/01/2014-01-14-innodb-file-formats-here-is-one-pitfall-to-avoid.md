---
title: 'InnoDB file formats: Here is one pitfall to avoid'
source:
  name: Percona Blog
  url: https://www.percona.com/blog/innodb-file-formats-here-is-one-pitfall-to-avoid/
  post_id: 7673
source_author:
  name: Fernando Laudares Camargos
  slug: fernando-laudares
  url: https://www.percona.com/blog/author/fernando-laudares/
  website: ''
published_at: '2014-01-14T14:00:34'
published_at_gmt: '2014-01-14T14:00:34'
modified_at: '2026-03-25T17:17:41'
modified_at_gmt: '2026-03-25T17:17:41'
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
- Antelope
- barracuda
- information_schema
- InnoDB file formats
tag_slugs:
- antelope
- barracuda
- information_schema
- innodb-file-formats
featured_image_url: ''
image_count: 0
graph_or_chart_count: 0
content_import: metadata_excerpt_headings_and_source_image_urls_only
copyright_notice: Percona All Rights Reserved; full article text and local image copies are not reproduced.
---

# InnoDB file formats: Here is one pitfall to avoid

Source: [Percona Blog](https://www.percona.com/blog/innodb-file-formats-here-is-one-pitfall-to-avoid/)

Auteur source: [Fernando Laudares Camargos](https://www.percona.com/blog/author/fernando-laudares/)

Publication: 2014-01-14T14:00:34

Curateur PmaControl: [Sylvain ARBAUDIE](../../../authors/sylvain-arbaudie.md)

## Note de droits

Fiche de veille uniquement: les metadonnees, l'extrait public, les titres de sections detectes et les URLs des visuels sont conserves. Le texte complet et les copies locales des images restent sur la source Percona.

## Extrait public

UPDATED: explaining the role of innodb_strict_mode and correcting introduction of innodb_file_format Compressed tables is an example of an InnoDB feature that became available with the Barracuda file format, introduced in the InnoDB plugin. They can bring significant gains in raw performance and scalability: given the data is stored in a compressed format the amount of … Continued

## Structure detectee

- H2: Conclusion

## Auteur source

Fernando Laudares Camargos joined Percona in early 2013 after working 8 years for a Canadian company specialized in offering services based in open source technologies. Fernando's work experience includes the architecture, deployment and maintenance of IT infrastructures based on Linux, open source software and a layer of server virtualization. From the basic services such as DHCP & DNS to identity management systems, but also including backup routines, configuration management tools and thin-clients. He's now focusing on the universe of MySQL, MongoDB and PostgreSQL with a particular interest in understanding the intricacies of database systems and contributes regularly to this blog. You can read his other articles here.
