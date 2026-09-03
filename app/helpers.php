<?php

if (! function_exists('sponsor_package_label')) {
    /**
     * Display label for a sponsor package tier.
     *
     * The top tier is stored in the database as "platinum" for historical
     * reasons, but DMC no longer uses that name — it's called "Major".
     * This maps the raw stored value to the label shown to users, without
     * touching the underlying "platinum" value anywhere in the database,
     * queries, or comparisons.
     */
    function sponsor_package_label($package)
    {
        return $package === 'platinum' ? 'Major' : ucfirst($package ?? '');
    }
}
