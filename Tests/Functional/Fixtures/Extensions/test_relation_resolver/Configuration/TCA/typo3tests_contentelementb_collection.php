<?php

return [
    'ctrl' => [
        'title' => 'collection',
        'label' => 'fieldA',
        'hideTable' => true,
        'enablecolumns' => [
            'disabled' => 'hidden',
            'starttime' => 'starttime',
            'endtime' => 'endtime',
            'fe_group' => 'fe_group',
        ],
        'editlock' => 'editlock',
        'delete' => 'deleted',
        'crdate' => 'crdate',
        'tstamp' => 'tstamp',
        'versioningWS' => true,
        'sortby' => 'sorting',
        'security' => [
            'ignorePageTypeRestriction' => true,
        ],
        'transOrigPointerField' => 'l10n_parent',
        'translationSource' => 'l10n_source',
        'transOrigDiffSourceField' => 'l10n_diffsource',
        'languageField' => 'sys_language_uid',
        'typeicon_classes' => [
            'default' => 'typo3tests_contentelementb_collection-1-116cf86',
        ],
        'searchFields' => 'fieldA',
    ],
    'palettes' => [
        'language' => [
            'showitem' => 'sys_language_uid,l10n_parent',
        ],
        'hidden' => [
            'label' => 'LLL:EXT:frontend/Resources/Private/Language/locallang_tca.xlf:pages.palettes.visibility',
            'showitem' => 'hidden',
        ],
        'access' => [
            'label' => 'LLL:EXT:frontend/Resources/Private/Language/locallang_ttc.xlf:palette.access',
            'showitem' => 'starttime;LLL:EXT:frontend/Resources/Private/Language/locallang_ttc.xlf:starttime_formlabel,endtime;LLL:EXT:frontend/Resources/Private/Language/locallang_ttc.xlf:endtime_formlabel,--linebreak--,fe_group;LLL:EXT:frontend/Resources/Private/Language/locallang_ttc.xlf:fe_group_formlabel,--linebreak--,editlock',
        ],
    ],
    'columns' => [
        'foreign_table_parent_uid' => [
            'config' => [
                'type' => 'passthrough',
            ],
        ],
        'fieldA' => [
            'label' => 'fieldA',
            'exclude' => true,
            'config' => [
                'type' => 'input',
            ],
        ],
        'flexA' => [
            'label' => 'flexA',
            'exclude' => true,
            'config' => [
                'type' => 'flex',
                'ds' => [
                    'default' => '<T3FlexForms>
    <sheets type="array">
        <sheet type="array">
            <ROOT type="array">
                <type>array</type>
                <el type="array">
                    <field index="link" type="array">
                        <label>header</label>
                        <config type="array">
                            <type>link</type>
                        </config>
                    </field>
                    <field index="datetime" type="array">
                        <label>datetime</label>
                        <config type="array">
                            <type>datetime</type>
                        </config>
                    </field>
                    <field index="some.number" type="array">
                        <label>number</label>
                        <config type="array">
                            <type>number</type>
                        </config>
                    </field>
                    <field index="some.link" type="array">
                        <label>link 2</label>
                        <config type="array">
                            <type>link</type>
                        </config>
                    </field>
                </el>
            </ROOT>
        </sheet>
    </sheets>
</T3FlexForms>',
                ],
            ],
        ],
    ],
    'types' => [
        1 => [
            'showitem' => '--div--;LLL:EXT:core/Resources/Private/Language/Form/locallang_tabs.xlf:general,fieldA,flexA,--div--;LLL:EXT:core/Resources/Private/Language/Form/locallang_tabs.xlf:language,--palette--;;language,--div--;LLL:EXT:core/Resources/Private/Language/Form/locallang_tabs.xlf:access,--palette--;;hidden,--palette--;;access',
        ],
    ],
];
