<?php

/**
 * Contao Open Source CMS
 *
 * @copyright  inspiredminds.at 2016
 * @author     Fritz Michael Gschwantner <fmg@inspiredminds.at>
 * @package    austrian_districts
 */


/**
 * Class FormAustrianDistricts
 */
class FormAustrianDistricts extends \FormSelectMenu
{

    /**
     * Path to the district cache file
     *
     * @var string
     */
    protected static $strCacheFile = 'system/cache/austrian_districts.json';


    /**
     * Initialize the object
     *
     * @param array $arrAttributes An optional attributes array
     */
    public function __construct( $arrAttributes = null )
    {
        // call parent constructor
        parent::__construct( $arrAttributes );

        // include empty value
        $this->arrOptions[] = array(array('value' => '', 'label' => ''));

        // go through the districts
        foreach( self::getDistricts() as $strCountry => $arrDistricts )
        {
            $this->arrOptions[] = array(
                'group' => true,
                'label' => $strCountry
            );

            foreach( $arrDistricts as $strDistrict )
            {
                $this->arrOptions[] = array(
                    'value' => \StringUtil::generateAlias($strCountry . '_' . $strDistrict),
                    'label' => $strDistrict
                );
            }
        }
    }


    /**
     * Add specific attributes
     *
     * @param string $strKey   The attribute name
     * @param mixed  $varValue The attribute value
     */
    public function __set($strKey, $varValue)
    {
        switch ($strKey)
        {
            case 'options':
                // Ignore
                break;

            default:
                parent::__set($strKey, $varValue);
                break;
        }
    }


    /**
     * Returns the available districts as array.
     *
     * @return array
     */
    protected static function getDistricts()
    {
        // load the cached result
        if( file_exists( TL_ROOT . '/' . self::$strCacheFile ) )
        {
            $objFile = new \File( self::$strCacheFile, true );
            return json_decode( $objFile->getContent(), true );
        }

        // prepare results
        $arrDistricts = array();

        // load from server
        $arrData = file( 'http://www.statistik.at/verzeichnis/reglisten/polbezirke.csv', FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES );

        // process the data
        for( $i = 3; $i < count( $arrData ) - 1; ++$i )
        {
            // get the line
            $arrLine = explode(';', utf8_encode( $arrData[$i] ) );

            // check the line
            if( !$arrLine )
            {
                continue;
            }

            // get the country label
            $strCountry = $arrLine[1];

            // check the country label
            if( !$strCountry )
            {
                continue;
            }

            // get the district label
            $strDistrict = $arrLine[3];

            // check the district label
            if( !$strDistrict )
            {
                continue;
            }

            // check if we have country already
            if( !isset( $arrDistricts[ $strCountry ] ) )
            {
                $arrDistricts[ $strCountry ] = array();
            }

            // add district
            $arrDistricts[ $strCountry ][] = $strDistrict;
        }

        // update cache
        if( $arrDistricts )
        {
            \File::putContent( self::$strCacheFile, json_encode( $arrDistricts ) );
        }

        // return the result
        return $arrDistricts;
    }
}
