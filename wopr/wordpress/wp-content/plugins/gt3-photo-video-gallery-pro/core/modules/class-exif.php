<?php

namespace GT3\Core;

class Exif {
	protected $data = array();
	protected $exif = array();

	const ExposureTime                = 'ExposureTime';
	const FNumber                     = 'FNumber';
	const ExposureProgram             = 'ExposureProgram';
	const SpectralSensitivity         = 'SpectralSensitivity';
	const ISO                         = 'ISO';
	const TimeZoneOffset              = 'TimeZoneOffset';
	const SelfTimerMode               = 'SelfTimerMode';
	const SensitivityType             = 'SensitivityType';
	const StandardOutputSensitivity   = 'StandardOutputSensitivity';
	const RecommendedExposureIndex    = 'RecommendedExposureIndex';
	const ISOSpeed                    = 'ISOSpeed';
	const ISOSpeedLatitudeyyy         = 'ISOSpeedLatitudeyyy';
	const ISOSpeedLatitudezzz         = 'ISOSpeedLatitudezzz';
	const ExifVersion                 = 'ExifVersion';
	const DateTimeOriginal            = 'DateTimeOriginal';
	const CreateDate                  = 'CreateDate';
	const GooglePlusUploadCode        = 'GooglePlusUploadCode';
	const OffsetTime                  = 'OffsetTime';
	const OffsetTimeOriginal          = 'OffsetTimeOriginal';
	const OffsetTimeDigitized         = 'OffsetTimeDigitized';
	const ComponentsConfiguration     = 'ComponentsConfiguration';
	const CompressedBitsPerPixel      = 'CompressedBitsPerPixel';
	const ShutterSpeedValue           = 'ShutterSpeedValue';
	const ApertureValue               = 'ApertureValue';
	const BrightnessValue             = 'BrightnessValue';
	const ExposureCompensation        = 'ExposureCompensation';
	const MaxApertureValue            = 'MaxApertureValue';
	const SubjectDistance             = 'SubjectDistance';
	const MeteringMode                = 'MeteringMode';
	const LightSource                 = 'LightSource';
	const Flash                       = 'Flash';
	const FocalLength                 = 'FocalLength';
	const ImageNumber                 = 'ImageNumber';
	const SecurityClassification      = 'SecurityClassification';
	const ImageHistory                = 'ImageHistory';
	const SubjectArea                 = 'SubjectArea';
	const UserComment                 = 'UserComment';
	const SubSecTime                  = 'SubSecTime';
	const SubSecTimeOriginal          = 'SubSecTimeOriginal';
	const SubSecTimeDigitized         = 'SubSecTimeDigitized';
	const AmbientTemperature          = 'AmbientTemperature';
	const Humidity                    = 'Humidity';
	const Pressure                    = 'Pressure';
	const WaterDepth                  = 'WaterDepth';
	const Acceleration                = 'Acceleration';
	const CameraElevationAngle        = 'CameraElevationAngle';
	const FlashpixVersion             = 'FlashpixVersion';
	const ColorSpace                  = 'ColorSpace';
	const ExifImageWidth              = 'ExifImageWidth';
	const ExifImageHeight             = 'ExifImageHeight';
	const RelatedSoundFile            = 'RelatedSoundFile';
	const FlashEnergy                 = 'FlashEnergy';
	const FocalPlaneXResolution       = 'FocalPlaneXResolution';
	const FocalPlaneYResolution       = 'FocalPlaneYResolution';
	const FocalPlaneResolutionUnit    = 'FocalPlaneResolutionUnit';
	const SubjectLocation             = 'SubjectLocation';
	const ExposureIndex               = 'ExposureIndex';
	const SensingMethod               = 'SensingMethod';
	const FileSource                  = 'FileSource';
	const SceneType                   = 'SceneType';
	const CFAPattern                  = 'CFAPattern';
	const CustomRendered              = 'CustomRendered';
	const ExposureMode                = 'ExposureMode';
	const WhiteBalance                = 'WhiteBalance';
	const DigitalZoomRatio            = 'DigitalZoomRatio';
	const FocalLengthIn35mmFormat     = 'FocalLengthIn35mmFormat';
	const SceneCaptureType            = 'SceneCaptureType';
	const GainControl                 = 'GainControl';
	const Contrast                    = 'Contrast';
	const Saturation                  = 'Saturation';
	const Sharpness                   = 'Sharpness';
	const SubjectDistanceRange        = 'SubjectDistanceRange';
	const ImageUniqueID               = 'ImageUniqueID';
	const OwnerName                   = 'OwnerName';
	const SerialNumber                = 'SerialNumber';
	const LensInfo                    = 'LensInfo';
	const LensMake                    = 'LensMake';
	const LensModel                   = 'LensModel';
	const LensSerialNumber            = 'LensSerialNumber';
	const CompositeImage              = 'CompositeImage';
	const CompositeImageCount         = 'CompositeImageCount';
	const CompositeImageExposureTimes = 'CompositeImageExposureTimes';
	const Gamma                       = 'Gamma';
	const Padding                     = 'Padding';
	const OffsetSchema                = 'OffsetSchema';
	const Lens                        = 'Lens';
	const RawFile                     = 'RawFile';
	const Converter                   = 'Converter';
	const Exposure                    = 'Exposure';
	const Shadows                     = 'Shadows';
	const Brightness                  = 'Brightness';
	const Smoothness                  = 'Smoothness';
	const MoireFilter                 = 'MoireFilter';

	private $keys = array(
		self::ExposureTime                => array( 'UndefinedTag:0x829A' ),
		self::FNumber                     => array( 'UndefinedTag:0x829D' ),
		self::ExposureProgram             => array( 'UndefinedTag:0x8822' ),
		self::SpectralSensitivity         => array( 'UndefinedTag:0x8824' ),
		self::ISO                         => array( 'ISOSpeedRatings', 'UndefinedTag:0x8827' ),
		self::TimeZoneOffset              => array( 'UndefinedTag:0x882A' ),
		self::SelfTimerMode               => array( 'UndefinedTag:0x882B' ),
		self::SensitivityType             => array( 'UndefinedTag:0x8830' ),
		self::StandardOutputSensitivity   => array( 'UndefinedTag:0x8831' ),
		self::RecommendedExposureIndex    => array( 'UndefinedTag:0x8832' ),
		self::ISOSpeed                    => array( 'UndefinedTag:0x8833' ),
		self::ISOSpeedLatitudeyyy         => array( 'UndefinedTag:0x8834' ),
		self::ISOSpeedLatitudezzz         => array( 'UndefinedTag:0x8835' ),
		self::ExifVersion                 => array( 'UndefinedTag:0x9000' ),
		self::DateTimeOriginal            => array( 'UndefinedTag:0x9003' ),
		self::CreateDate                  => array( 'UndefinedTag:0x9004' ),
		self::GooglePlusUploadCode        => array( 'UndefinedTag:0x9009' ),
		self::OffsetTime                  => array( 'UndefinedTag:0x9010' ),
		self::OffsetTimeOriginal          => array( 'UndefinedTag:0x9011' ),
		self::OffsetTimeDigitized         => array( 'UndefinedTag:0x9012' ),
		self::ComponentsConfiguration     => array( 'UndefinedTag:0x9101' ),
		self::CompressedBitsPerPixel      => array( 'UndefinedTag:0x9102' ),
		self::ShutterSpeedValue           => array( 'UndefinedTag:0x9201' ),
		self::ApertureValue               => array( 'UndefinedTag:0x9202' ),
		self::BrightnessValue             => array( 'UndefinedTag:0x9203' ),
		self::ExposureCompensation        => array( 'UndefinedTag:0x9204' ),
		self::MaxApertureValue            => array( 'UndefinedTag:0x9205' ),
		self::SubjectDistance             => array( 'UndefinedTag:0x9206' ),
		self::MeteringMode                => array( 'UndefinedTag:0x9207' ),
		self::LightSource                 => array( 'UndefinedTag:0x9208' ),
		self::Flash                       => array( 'UndefinedTag:0x9209' ),
		self::FocalLength                 => array( 'UndefinedTag:0x920A' ),
		self::ImageNumber                 => array( 'UndefinedTag:0x9211' ),
		self::SecurityClassification      => array( 'UndefinedTag:0x9212' ),
		self::ImageHistory                => array( 'UndefinedTag:0x9213' ),
		self::SubjectArea                 => array( 'UndefinedTag:0x9214' ),
		self::UserComment                 => array( 'UndefinedTag:0x9286' ),
		self::SubSecTime                  => array( 'UndefinedTag:0x9290' ),
		self::SubSecTimeOriginal          => array( 'UndefinedTag:0x9291' ),
		self::SubSecTimeDigitized         => array( 'UndefinedTag:0x9292' ),
		self::AmbientTemperature          => array( 'UndefinedTag:0x9400' ),
		self::Humidity                    => array( 'UndefinedTag:0x9401' ),
		self::Pressure                    => array( 'UndefinedTag:0x9402' ),
		self::WaterDepth                  => array( 'UndefinedTag:0x9403' ),
		self::Acceleration                => array( 'UndefinedTag:0x9404' ),
		self::CameraElevationAngle        => array( 'UndefinedTag:0x9405' ),
		self::FlashpixVersion             => array( 'UndefinedTag:0xA000' ),
		self::ColorSpace                  => array( 'UndefinedTag:0xA001' ),
		self::ExifImageWidth              => array( 'UndefinedTag:0xA002' ),
		self::ExifImageHeight             => array( 'UndefinedTag:0xA003' ),
		self::RelatedSoundFile            => array( 'UndefinedTag:0xA004' ),
		self::FlashEnergy                 => array( 'UndefinedTag:0xA20B' ),
		self::FocalPlaneXResolution       => array( 'UndefinedTag:0xA20E' ),
		self::FocalPlaneYResolution       => array( 'UndefinedTag:0xA20F' ),
		self::FocalPlaneResolutionUnit    => array( 'UndefinedTag:0xA210' ),
		self::SubjectLocation             => array( 'UndefinedTag:0xA214' ),
		self::ExposureIndex               => array( 'UndefinedTag:0xA215' ),
		self::SensingMethod               => array( 'UndefinedTag:0xA217' ),
		self::FileSource                  => array( 'UndefinedTag:0xA300' ),
		self::SceneType                   => array( 'UndefinedTag:0xA301' ),
		self::CFAPattern                  => array( 'UndefinedTag:0xA302' ),
		self::CustomRendered              => array( 'UndefinedTag:0xA401' ),
		self::ExposureMode                => array( 'UndefinedTag:0xA402' ),
		self::WhiteBalance                => array( 'UndefinedTag:0xFE4E' ),
		self::DigitalZoomRatio            => array( 'UndefinedTag:0xA404' ),
		self::FocalLengthIn35mmFormat     => array( 'UndefinedTag:0xA405' ),
		self::SceneCaptureType            => array( 'UndefinedTag:0xA406' ),
		self::GainControl                 => array( 'UndefinedTag:0xA407' ),
		self::Contrast                    => array( 'UndefinedTag:0xFE54' ),
		self::Saturation                  => array( 'UndefinedTag:0xFE55' ),
		self::Sharpness                   => array( 'UndefinedTag:0xFE56' ),
		self::SubjectDistanceRange        => array( 'UndefinedTag:0xA40C' ),
		self::ImageUniqueID               => array( 'UndefinedTag:0xA420' ),
		self::OwnerName                   => array( 'UndefinedTag:0xFDE8' ),
		self::SerialNumber                => array( 'UndefinedTag:0xFDE9' ),
		self::LensInfo                    => array( 'UndefinedTag:0xA432' ),
		self::LensMake                    => array( 'UndefinedTag:0xA433' ),
		self::LensModel                   => array( 'UndefinedTag:0xA434' ),
		self::LensSerialNumber            => array( 'UndefinedTag:0xA435' ),
		self::CompositeImage              => array( 'UndefinedTag:0xA460' ),
		self::CompositeImageCount         => array( 'UndefinedTag:0xA461' ),
		self::CompositeImageExposureTimes => array( 'UndefinedTag:0xA462' ),
		self::Gamma                       => array( 'UndefinedTag:0xA500' ),
		self::Padding                     => array( 'UndefinedTag:0xEA1C' ),
		self::OffsetSchema                => array( 'UndefinedTag:0xEA1D' ),
		self::Lens                        => array( 'UndefinedTag:0xFDEA' ),
		self::RawFile                     => array( 'UndefinedTag:0xFE4C' ),
		self::Converter                   => array( 'UndefinedTag:0xFE4D' ),
		self::Exposure                    => array( 'UndefinedTag:0xFE51' ),
		self::Shadows                     => array( 'UndefinedTag:0xFE52' ),
		self::Brightness                  => array( 'UndefinedTag:0xFE53' ),
		self::Smoothness                  => array( 'UndefinedTag:0xFE57' ),
		self::MoireFilter                 => array( 'UndefinedTag:0xFE58' ),
	);

	public function __construct($file){
		if(!function_exists('exif_read_data')) {
			add_action('admin_notices', array( $this, 'admin_notices' ));

			return;
		}

		$this->data = exif_read_data($file);
		$this->get('Model');
		$this->get('LensModel');
		$this->get('FocalLength');
		$this->get('ExposureTime');
		$this->get('ISOSpeedRatings');

		$this->exif = array(
			'ShutterSpeedValue' => $this->get_ShutterSpeedValue(),
			'ApertureValue'     => $this->get_Aperture(),
		);
	}

	protected function find($key){
		$value = false;
		if(key_exists($key, $this->data)) {
			$value = $this->data[$key];
		} else {
			if(key_exists($key, $this->keys)) {
				foreach((array)$this->keys[$key] as $_key) {
					if (method_exists($this,$_key)) {
						$value = call_user_func(array($this,$_key), $_key);
					} else if (method_exists($this,'get_'.$_key)) {
						$value = call_user_func(array($this,'get_'.$_key), $_key);
					} else if(key_exists($_key, $this->data)) {
						$value = $this->data[$_key];
					}
				}
			}
		}

		return $value;
	}

	public function get_exif(){
		return $this->exif;
	}

	public function get_Aperture(){
		$value = $this->find('ApertureValue');
		if(false !== $value) {
			list($a, $b) = explode('/', $value);
			$value = number_format(sqrt(pow(2, (float) $a/(float) $b)), 1, '.', '');;
		}

		return $value;
	}

	public function get_ShutterSpeedValue(){
		$value = $this->find('ShutterSpeedValue');
		if(false !== $value) {
			list($a, $b) = explode('/', $value);
			$value = '1/'.pow(2, $a/$b);
		}

		return $value;
	}

	public function get($key){
		if(key_exists($key, $this->exif)) {
			return $this->exif[$key];
		}

		$this->exif[$key] = $this->find($key);

		return $this->exif[$key];
	}


	public function admin_notice(){
		$msg   = sprintf(esc_html('Function %s not callable. Install module or allow function to use EXIF meta.'), 'exif_read_data');
		$class = 'notice notice-warning gt3pg_error_notice';
		echo '<div class="'.esc_attr($class).'"><p>'.$msg.'</p></div>';
	}

}
