---
title: Finding Undetected Jumbo Chunks in MongoDB
source:
  name: Percona Blog
  url: https://www.percona.com/blog/finding-undetected-jumbo-chunks-in-mongodb/
  post_id: 24797
source_author:
  name: Ivan Groenewold
  slug: ivan-groenewold
  url: https://www.percona.com/blog/author/ivan-groenewold/
  website: ''
published_at: '2021-09-10T12:02:29'
published_at_gmt: '2021-09-10T12:02:29'
modified_at: '2026-03-26T20:15:06'
modified_at_gmt: '2026-03-26T20:15:06'
curator:
  name: Sylvain ARBAUDIE
  slug: sylvain-arbaudie
  source_url: https://www.pmacontrol.fr/fr/site/blog_author/sylvain-arbaudie/
matched_topics:
- PMM
matched_filters:
- search:pmm
categories:
- Insight for DBAs
- MongoDB
category_slugs:
- insight-for-dbas
- mongodb
tags:
- MongoDB
- sharding
tag_slugs:
- mongodb
- sharding
featured_image_url: https://www.percona.com/wp-content/uploads/2026/03/Jumbo-Chunks-in-MongoDB.png
image_count: 2
graph_or_chart_count: 0
content_import: metadata_excerpt_headings_and_source_image_urls_only
copyright_notice: Percona All Rights Reserved; full article text and local image copies are not reproduced.
---

# Finding Undetected Jumbo Chunks in MongoDB

Source: [Percona Blog](https://www.percona.com/blog/finding-undetected-jumbo-chunks-in-mongodb/)

Auteur source: [Ivan Groenewold](https://www.percona.com/blog/author/ivan-groenewold/)

Publication: 2021-09-10T12:02:29

Curateur PmaControl: [Sylvain ARBAUDIE](../../../authors/sylvain-arbaudie.md)

## Note de droits

Fiche de veille uniquement: les metadonnees, l'extrait public, les titres de sections detectes et les URLs des visuels sont conserves. Le texte complet et les copies locales des images restent sur la source Percona.

## Extrait public

I recently came across an interesting case of performance issues during balancing in a MongoDB cluster. Digging through the logs, it became clear the problem was related to chunk moves taking a long time. As we know, the default maximum chunk size is 64 MB. So these migrations are supposed to be very fast in … Continued

## Structure detectee

- H2: Recap on Chunk Moves
- H2: The autoSplitter in Action
- H2: Finding Undetected Jumbo Chunks in MongoDB
- H3: Conclusion

## Images et graphiques reperes

- featured / image: [Finding Undetected Jumbo Chunks in MongoDB](https://www.percona.com/wp-content/uploads/2026/03/Jumbo-Chunks-in-MongoDB.png)
- content / image: [Jumbo Chunks in MongoDB](https://www.percona.com/wp-content/uploads/2026/03/Jumbo-Chunks-in-MongoDB-300x157.png)

## Auteur source

Passionate about technology, Ivan Groenewold is a seasoned professional with extensive experience in database management, cloud infrastructure, and software development. With a focus on optimizing performance and scalability, Ivan excels in designing and implementing solutions for complex systems, particularly in MongoDB and cloud-native environments. Known for problem-solving and a results-driven mindset, Ivan combines technical expertise with a commitment to continuous learning, delivering high-quality solutions for modern enterprise applications.
