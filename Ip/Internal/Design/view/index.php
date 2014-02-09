<?php
/* @var $theme \Ip\Internal\Design\Theme */
/* @var $this \Ip\View */
?>
<div class="ip ipModuleDesign" xmlns="http://www.w3.org/1999/html">
    <h1><?php _e('My theme', 'ipAdmin'); ?></h1>

    <div class="ipmSelectedTheme">
        <div class="ipmThemePreview">
            <img src="<?php echo esc($theme->getThumbnailUrl()); ?>" alt="<?php echo esc($theme->getTitle()); ?>" />
        </div>

        <div class="ipmThemeActions">
<!--            <a href="#" class="btn btn-link">Download</a>-->
            <?php if ($showConfiguration){ ?>
                <a href="#" class="btn btn-primary ipsOpenOptions"><?php _e('Options', 'ipAdmin'); ?></a>
                <br/><br/>
            <?php } ?>
            <a href="<?php echo $contentManagementUrl ?>" class="btn btn-primary"><?php echo esc($contentManagementText); ?></a>
        </div>
        <h2>
            <i class="fa fa-check"></i>
            <?php echo esc($theme->getTitle()); ?>
            <small>(<?php echo esc($theme->getVersion()); ?>)</small>
        </h2>
        <div class="ipmPlugins">
            <?php if ($pluginNote) { ?>
            <div class="alert alert-block">
                <?php echo esc($pluginNote); ?>
            </div>
            <?php } ?>
            <dl class="dl-horizontal">
                <?php foreach ($plugins as $key => $plugin ) {?>
                    <dt><?php echo $key == 0 ? __('Available plugins', 'ipAdmin') . ':' : '' ?></dt>
                    <dd>
                        <?php echo esc(!empty($plugin['title']) ? $plugin['title'] : $plugin['name']); ?>
                        <a href="#" class="ipsInstallPlugin" data-pluginname="<?php echo esc($plugin['name']) ?>"><?php _e('Install', 'ipAdmin'); ?></a>
                    </dd>
                <?php } ?>
            </dl>
        </div>
    </div>

    <div class="ipmOtherThemes">
        <div class="ipmThemeMarket">
            <div class="ipmButtonWrapper">
                <span class="ipmTitle"><?php _e('Marketplace', 'ipAdmin'); ?></span>
                <span class="impNotice"><?php _e('Want a new look? Search for a new theme.', 'ipAdmin'); ?></span>
                <a href="#" class="btn btn-success ipsOpenMarket"><?php _e('Browse themes', 'ipAdmin'); ?></a>
            </div>
        </div>
        <div class="ipmLocalThemes">
            <?php if (count($availableThemes) > 1) { ?>
                <h2><?php _e('Local themes', 'ipAdmin'); ?></h2>
                <ul class="ipmThemesList clearfix">
                    <?php
                        foreach ($availableThemes as $localTheme) {
                            /* @var $localTheme \Ip\Internal\Design\Theme */
                            if ($localTheme->getName() == $theme->getName()) {
                                continue;
                            }
                    ?>
                            <li>
                                <div class="ipmThemePreview">
                                    <img src="<?php echo esc($localTheme->getThumbnailUrl()); ?>" alt="<?php echo esc($localTheme->getTitle()); ?>" />
                                </div>
                                <span class="ipmThemeTitle">
                                    <?php echo esc($localTheme->getTitle()); ?>
                                    <small>(<?php echo esc($localTheme->getVersion()); ?>)</small>
                                </span>
                                <div class="ipmThemeActions">
                                    <a href="#" class="btn btn-primary ipsInstallTheme" data-theme='<?php echo esc($localTheme->getName()) ?>'>
                                        <?php _e('Install', 'ipAdmin'); ?>
                                    </a>
                                </div>
                            </li>
                    <?php } ?>
                </ul>
            <?php } ?>
        </div>
    </div>

    <div class="ipsThemeMarketPopup ipmThemeMarketPopup ipgHide">
        <div class="ipmThemeMarketContainer" id="ipModuleThemeMarketContainer" data-marketurl="<?php echo esc($marketUrl) ?>">
            <a href="#" class="ipmThemeMarketPopupClose ipsThemeMarketPopupClose"><?php _e('Close', 'ipAdmin'); ?></a>
            <!-- <iframe name="easyXDM*" /> -->
        </div>
    </div>

    <div class="ipmPreview ipsPreview ipgHide">
        <button type="button" class="btn ipmPreviewClose ipsPreviewClose"><i class="fa fa-times"></i></button>
        <iframe class="ipsFrame" src="" frameborder="0"></iframe>
    </div>
</div>
