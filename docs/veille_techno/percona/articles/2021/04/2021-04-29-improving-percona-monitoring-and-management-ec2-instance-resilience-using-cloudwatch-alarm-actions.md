---
title: Improving Percona Monitoring and Management EC2 Instance Resilience Using CloudWatch Alarm Actions
source:
  name: Percona Blog
  url: https://www.percona.com/blog/improving-percona-monitoring-and-management-ec2-instance-resilience-using-cloudwatch-alarm-actions/
  post_id: 24223
source_author:
  name: Sergey Kuzmichev
  slug: sergey-kuzmichev
  url: https://www.percona.com/blog/author/sergey-kuzmichev/
  website: https://www.percona.com
published_at: '2021-04-29T17:52:35'
published_at_gmt: '2021-04-29T17:52:35'
modified_at: '2026-03-23T18:24:17'
modified_at_gmt: '2026-03-23T18:24:17'
curator:
  name: Sylvain ARBAUDIE
  slug: sylvain-arbaudie
  source_url: https://www.pmacontrol.fr/fr/site/blog_author/sylvain-arbaudie/
matched_topics:
- PMM
matched_filters:
- category:monitoring:2104
- search:percona-monitoring-and-management
- search:pmm
- tag:percona-monitoring-and-management:2166
- tag:pmm:2167
categories:
- Cloud
- Insight for DBAs
- Monitoring
- Percona Software
category_slugs:
- cloud
- insight-for-dbas
- monitoring
- percona-software
tags:
- AWS
- cloud
- Percona Monitoring and Management
- Percona Software
- PMM
tag_slugs:
- aws
- cloud
- percona-monitoring-and-management
- percona-software
- pmm
featured_image_url: https://www.percona.com/wp-content/uploads/2026/03/Percona-Monitoring-and-Management-EC2-Instance-Resilience-Using-CloudWatch.png
image_count: 11
graph_or_chart_count: 1
content_import: metadata_excerpt_headings_and_source_image_urls_only
copyright_notice: Percona All Rights Reserved; full article text and local image copies are not reproduced.
---

# Improving Percona Monitoring and Management EC2 Instance Resilience Using CloudWatch Alarm Actions

Source: [Percona Blog](https://www.percona.com/blog/improving-percona-monitoring-and-management-ec2-instance-resilience-using-cloudwatch-alarm-actions/)

Auteur source: [Sergey Kuzmichev](https://www.percona.com/blog/author/sergey-kuzmichev/)

Publication: 2021-04-29T17:52:35

Curateur PmaControl: [Sylvain ARBAUDIE](../../../authors/sylvain-arbaudie.md)

## Note de droits

Fiche de veille uniquement: les metadonnees, l'extrait public, les titres de sections detectes et les URLs des visuels sont conserves. Le texte complet et les copies locales des images restent sur la source Percona.

## Extrait public

Nothing lasts forever, including hardware running your EC2 instances. You will usually receive an advance warning on hardware degradation and subsequent instance retirement, but sometimes hardware fails unexpectedly. Percona Monitoring and Management (PMM) currently doesn’t have an HA setup, and such failures can leave wide gaps in monitoring if not resolved quickly. In this post, … Continued

## Structure detectee

- H2: Some Background
- H2: Automatically Recovering PMM on System Failure
- H2: Automatically Restarting PMM on Instance Failure
- H2: Setting up Alarms Using AWS CLI
- H2: Testing New Alarms
- H3: Summary
- H3: References

## Images et graphiques reperes

- featured / image: [Improving Percona Monitoring and Management EC2 Instance Resilience Using CloudWatch Alarm Actions](https://www.percona.com/wp-content/uploads/2026/03/Percona-Monitoring-and-Management-EC2-Instance-Resilience-Using-CloudWatch.png)
- content / image: [Percona Monitoring and Management EC2 Instance Resilience Using CloudWatch](https://www.percona.com/wp-content/uploads/2026/03/Percona-Monitoring-and-Management-EC2-Instance-Resilience-Using-CloudWatch-300x169.png)
- content / image: [ec2 instance overview showing 2/2 status checks](https://www.percona.com/wp-content/uploads/2026/03/pmmr_ec2-instance-1024x91.png)
- content / image: [EC2 console dropdown menu navigation to CloudWatch Alarms](https://www.percona.com/wp-content/uploads/2026/03/pmmr_ec2_console_add_alarm_menu.png)
- content / image: [Adding CloudWatch alarm through EC2 console interface](https://www.percona.com/wp-content/uploads/2026/03/pmmr_ec2_console_add_alarm.png)
- content / image: [EC2 instance restart alarm](https://www.percona.com/wp-content/uploads/2026/03/pmmr_alarm_set-1024x651.png)
- content / image: [Single alarm set in the instance overview](https://www.percona.com/wp-content/uploads/2026/03/pmmr_ec2-instance.single-alarm.png)
- content / image: [Adding CloudWatch alarm for instance failure through EC2 console interface](https://www.percona.com/wp-content/uploads/2026/03/pmmr_ec2_console_add_alarm.instance_failure.png)
- content / image: [EC2 instance overview showing 2 alarms set](https://www.percona.com/wp-content/uploads/2026/03/pmmr_ec2-instance.alarms-1024x90.png)
- content / image: [CloudWatch showing Alarm fired actions](https://www.percona.com/wp-content/uploads/2026/03/pmmr_alarm_fired.png)
- content / graph_or_chart: [CloudWatch metric overview](https://www.percona.com/wp-content/uploads/2026/03/pmmr_metric_per_minute-1024x368.png)

## Auteur source

Sergey is a support engineer in Percona. Interested in all things databases, he's currently working mainly with MySQL and PostgreSQL. He started his career working as an Oracle DBA, later moving to a DevOps engineer role supporting Java-based trading platform running on PostgreSQL. After being a jack of all trades for a while, he's now focusing on what he enjoys most: open source databases, systems performance, and reliability.
