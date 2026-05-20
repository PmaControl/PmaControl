---
title: 'How to Customize PagerDuty Custom Details in Grafana: The Hidden Override Method'
source:
  name: Percona Blog
  url: https://www.percona.com/blog/how-to-customize-pagerduty-custom-details-in-grafana-the-hidden-override-method/
  post_id: 35667
source_author:
  name: Tibor Korocz
  slug: tibor-koroczpercona-com
  url: https://www.percona.com/blog/author/tibor-koroczpercona-com/
  website: ''
published_at: '2026-03-13T13:53:19'
published_at_gmt: '2026-03-13T13:53:19'
modified_at: '2026-05-05T20:08:10'
modified_at_gmt: '2026-05-05T20:08:10'
curator:
  name: Sylvain ARBAUDIE
  slug: sylvain-arbaudie
  source_url: https://www.pmacontrol.fr/fr/site/blog_author/sylvain-arbaudie/
matched_topics:
- PMM
matched_filters:
- category:monitoring:2104
- search:pmm
categories:
- Monitoring
category_slugs:
- monitoring
tags:
- Grafana
tag_slugs:
- grafana
featured_image_url: https://www.percona.com/wp-content/uploads/2026/03/Cloud-native-database.jpg
image_count: 1
graph_or_chart_count: 0
content_import: metadata_excerpt_headings_and_source_image_urls_only
copyright_notice: Percona All Rights Reserved; full article text and local image copies are not reproduced.
---

# How to Customize PagerDuty Custom Details in Grafana: The Hidden Override Method

Source: [Percona Blog](https://www.percona.com/blog/how-to-customize-pagerduty-custom-details-in-grafana-the-hidden-override-method/)

Auteur source: [Tibor Korocz](https://www.percona.com/blog/author/tibor-koroczpercona-com/)

Publication: 2026-03-13T13:53:19

Curateur PmaControl: [Sylvain ARBAUDIE](../../../authors/sylvain-arbaudie.md)

## Note de droits

Fiche de veille uniquement: les metadonnees, l'extrait public, les titres de sections detectes et les URLs des visuels sont conserves. Le texte complet et les copies locales des images restent sur la source Percona.

## Extrait public

The Problem If you’ve integrated Grafana Alerting with PagerDuty, you’ve probably noticed something frustrating: the PagerDuty incident details are cluttered with every single label and annotation from your alerts. Here’s what you typically see: { "details": { "firing": " Value: A=-1, C=-1 Labels: - alertname = mysql_replication_lag - agent_id = 7903a5c7-5976-46d8-84d9-cabdec770353 - agent_type = mysqld_exporter - cluster = ps-replication-dev-cluster - environment = ps-replication-dev - grafana_folder = Experimental - instance = 7903a5c7-5976-46d8-84d9-cabdec770353 - job = mysqld_exporter_7903a5c7-5976-46d8-84d9-cabdec770353_mr - machine_id = 85a2cf29fa594a9e9a27aa88fce231ba - master_host = ps_pmm_replication_8_0_1 - master_uuid = 8da41e19-0667-11f1-adeb-9247e0067a2c - node_id = a1cc8cae-dd58-41e6-86eb-12c866fa4e8c - node_name = 79e48ca85f38 - node_type = generic - service_id = 01fb5b...

## Structure detectee

- H2: The Problem
- H2: The Documentation Gap
- H2: The Discovery Journey
- H3: Step 1: Investigating the PagerDuty Payload
- H3: Step 2: Experimenting with the Details Section
- H3: Step 3: The Breakthrough – Overriding “firing”
- H3: Step 4: Confirming with Source Code
- H2: The Solution: Override the Firing Key
- H3: Step 1: Navigate to Your PagerDuty Contact Point
- H3: Step 2: Add Custom Key-Value Pairs
- H3: Step 3: The Result
- H2: What about resolved incidents?
- H2: Which Keys Can You Override?
- H2: If you feel brave enough!
- H2: Advanced: Using Notification Templates
- H3: Create the Template
- H3: Use the Template in Details
- H2: Any workarounds?
- H2: Why This Matters
- H2: Conclusion

## Images et graphiques reperes

- featured / image: [How to Customize PagerDuty Custom Details in Grafana: The Hidden Override Method](https://www.percona.com/wp-content/uploads/2026/03/Cloud-native-database.jpg)

## Auteur source

Tibi joined Percona in 2015 as a Consultant. Before joining Percona, among many other things, he worked at the world’s largest car hire booking service as a Senior Database Engineer. He enjoys trying and working with the latest technologies and applications which can help or work with MySQL together. In his spare time he likes to spend time with his friends, travel around the world and play ultimate frisbee.
