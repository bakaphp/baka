# Baka Blameable 

## Table of Contents
1. [Blameable](#markdown-blameable)

## Blameable

```php
use Baka\Blameable\BlameableTrait;

class Leads extends Phalcon\Mvc\Model
{
    use BlameableTrait;

    /**
     * Initialize
     */
    public function initialize()
    {
        $this->keepSnapshots(true);
        $this->addBehavior(new \Baka\Blameable\Blameable());
    }
}
```

```sql
CREATE TABLE `audits` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `entity_id` int(10) UNSIGNED NOT NULL,
  `model_name` varchar(64) NOT NULL,
  `users_id` int(10) UNSIGNED NOT NULL,
  `ip` varchar(15) NOT NULL,
  `type` char(1) NOT NULL,
  `created_at` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Table structure for table `audits_details`
--

CREATE TABLE `audits_details` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `audits_id` bigint(20) UNSIGNED NOT NULL,
  `field_name` varchar(32) NOT NULL,
  `old_value` text,
  `old_value_text` text,
  `new_value` text,
  `new_value_text` text
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Indexes for dumped tables
--

--
-- Indexes for table `audits`
--
ALTER TABLE `audits`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx1` (`entity_id`),
  ADD KEY `idx2` (`model_name`),
  ADD KEY `idx3` (`users_id`),
  ADD KEY `idx4` (`type`),
  ADD KEY `idx5` (`model_name`,`type`),
  ADD KEY `idx6` (`entity_id`,`model_name`,`type`);

--
-- Indexes for table `audits_details`
--
ALTER TABLE `audits_details`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx1` (`audits_id`),
  ADD KEY `field_name` (`field_name`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `audits`
--
ALTER TABLE `audits`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `audits_details`
--
ALTER TABLE `audits_details`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;
COMMIT;
```