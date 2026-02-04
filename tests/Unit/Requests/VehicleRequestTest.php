<?php

namespace Tests\Unit\Requests;

use Tests\TestCase;
use App\Http\Requests\tenant\VehicleRequest;
use Illuminate\Support\Facades\Validator;

class VehicleRequestTest extends TestCase
{
    private function validate(array $data): \Illuminate\Validation\Validator
    {
        $request = new VehicleRequest();
        return Validator::make($data, $request->rules(), $request->messages());
    }

    public function testValidDataPasses(): void
    {
        $data = [
            'license_plate' => 'ABC-1234',
            'brand' => 'Toyota',
            'model' => 'Corolla',
            'year' => 2023,
            'client_id' => '01HXE7Q8N3K9Y2Z1P5V7M4W6J8',
        ];

        $validator = $this->validate($data);

        $this->assertTrue($validator->passes());
    }

    public function testValidDataWithMercosulPlatePasses(): void
    {
        $data = [
            'license_plate' => 'ABC1D23',
            'brand' => 'Honda',
            'model' => 'Civic',
            'year' => 2022,
            'client_id' => '01HXE7Q8N3K9Y2Z1P5V7M4W6J8',
        ];

        $validator = $this->validate($data);

        $this->assertTrue($validator->passes());
    }

    public function testBrandIsRequired(): void
    {
        $data = [
            'license_plate' => 'ABC-1234',
            'model' => 'Corolla',
            'year' => 2023,
            'client_id' => '01HXE7Q8N3K9Y2Z1P5V7M4W6J8',
        ];

        $validator = $this->validate($data);

        $this->assertFalse($validator->passes());
        $this->assertTrue($validator->errors()->has('brand'));
    }

    public function testBrandMinLength(): void
    {
        $data = [
            'license_plate' => 'ABC-1234',
            'brand' => 'AB',
            'model' => 'Corolla',
            'year' => 2023,
            'client_id' => '01HXE7Q8N3K9Y2Z1P5V7M4W6J8',
        ];

        $validator = $this->validate($data);

        $this->assertFalse($validator->passes());
        $this->assertTrue($validator->errors()->has('brand'));
    }

    public function testBrandMaxLength(): void
    {
        $data = [
            'license_plate' => 'ABC-1234',
            'brand' => str_repeat('A', 256),
            'model' => 'Corolla',
            'year' => 2023,
            'client_id' => '01HXE7Q8N3K9Y2Z1P5V7M4W6J8',
        ];

        $validator = $this->validate($data);

        $this->assertFalse($validator->passes());
        $this->assertTrue($validator->errors()->has('brand'));
    }

    public function testModelIsRequired(): void
    {
        $data = [
            'license_plate' => 'ABC-1234',
            'brand' => 'Toyota',
            'year' => 2023,
            'client_id' => '01HXE7Q8N3K9Y2Z1P5V7M4W6J8',
        ];

        $validator = $this->validate($data);

        $this->assertFalse($validator->passes());
        $this->assertTrue($validator->errors()->has('model'));
    }

    public function testModelMaxLength(): void
    {
        $data = [
            'license_plate' => 'ABC-1234',
            'brand' => 'Toyota',
            'model' => str_repeat('A', 256),
            'year' => 2023,
            'client_id' => '01HXE7Q8N3K9Y2Z1P5V7M4W6J8',
        ];

        $validator = $this->validate($data);

        $this->assertFalse($validator->passes());
        $this->assertTrue($validator->errors()->has('model'));
    }

    public function testYearIsRequired(): void
    {
        $data = [
            'license_plate' => 'ABC-1234',
            'brand' => 'Toyota',
            'model' => 'Corolla',
            'client_id' => '01HXE7Q8N3K9Y2Z1P5V7M4W6J8',
        ];

        $validator = $this->validate($data);

        $this->assertFalse($validator->passes());
        $this->assertTrue($validator->errors()->has('year'));
    }

    public function testYearMustBeInteger(): void
    {
        $data = [
            'license_plate' => 'ABC-1234',
            'brand' => 'Toyota',
            'model' => 'Corolla',
            'year' => 'not-a-number',
            'client_id' => '01HXE7Q8N3K9Y2Z1P5V7M4W6J8',
        ];

        $validator = $this->validate($data);

        $this->assertFalse($validator->passes());
        $this->assertTrue($validator->errors()->has('year'));
    }

    public function testYearMinValue(): void
    {
        $data = [
            'license_plate' => 'ABC-1234',
            'brand' => 'Toyota',
            'model' => 'Corolla',
            'year' => 1885,
            'client_id' => '01HXE7Q8N3K9Y2Z1P5V7M4W6J8',
        ];

        $validator = $this->validate($data);

        $this->assertFalse($validator->passes());
        $this->assertTrue($validator->errors()->has('year'));
    }

    public function testYearMaxValue(): void
    {
        $data = [
            'license_plate' => 'ABC-1234',
            'brand' => 'Toyota',
            'model' => 'Corolla',
            'year' => date('Y') + 2,
            'client_id' => '01HXE7Q8N3K9Y2Z1P5V7M4W6J8',
        ];

        $validator = $this->validate($data);

        $this->assertFalse($validator->passes());
        $this->assertTrue($validator->errors()->has('year'));
    }

    public function testClientIdIsRequired(): void
    {
        $data = [
            'license_plate' => 'ABC-1234',
            'brand' => 'Toyota',
            'model' => 'Corolla',
            'year' => 2023,
        ];

        $validator = $this->validate($data);

        $this->assertFalse($validator->passes());
        $this->assertTrue($validator->errors()->has('client_id'));
    }

    public function testClientIdMaxLength(): void
    {
        $data = [
            'license_plate' => 'ABC-1234',
            'brand' => 'Toyota',
            'model' => 'Corolla',
            'year' => 2023,
            'client_id' => str_repeat('A', 27),
        ];

        $validator = $this->validate($data);

        $this->assertFalse($validator->passes());
        $this->assertTrue($validator->errors()->has('client_id'));
    }

    public function testLicensePlateIsRequired(): void
    {
        $data = [
            'brand' => 'Toyota',
            'model' => 'Corolla',
            'year' => 2023,
            'client_id' => '01HXE7Q8N3K9Y2Z1P5V7M4W6J8',
        ];

        $validator = $this->validate($data);

        $this->assertFalse($validator->passes());
        $this->assertTrue($validator->errors()->has('license_plate'));
    }

    public function testLicensePlateOldFormat(): void
    {
        $data = [
            'license_plate' => 'ABC-1234',
            'brand' => 'Toyota',
            'model' => 'Corolla',
            'year' => 2023,
            'client_id' => '01HXE7Q8N3K9Y2Z1P5V7M4W6J8',
        ];

        $validator = $this->validate($data);

        $this->assertTrue($validator->passes());
    }

    public function testLicensePlateOldFormatWithoutDash(): void
    {
        $data = [
            'license_plate' => 'ABC1234',
            'brand' => 'Toyota',
            'model' => 'Corolla',
            'year' => 2023,
            'client_id' => '01HXE7Q8N3K9Y2Z1P5V7M4W6J8',
        ];

        $validator = $this->validate($data);

        $this->assertTrue($validator->passes());
    }

    public function testLicensePlateMercosulFormat(): void
    {
        $data = [
            'license_plate' => 'ABC1D23',
            'brand' => 'Toyota',
            'model' => 'Corolla',
            'year' => 2023,
            'client_id' => '01HXE7Q8N3K9Y2Z1P5V7M4W6J8',
        ];

        $validator = $this->validate($data);

        $this->assertTrue($validator->passes());
    }

    public function testLicensePlateInvalidFormat(): void
    {
        $data = [
            'license_plate' => '123-ABCD',
            'brand' => 'Toyota',
            'model' => 'Corolla',
            'year' => 2023,
            'client_id' => '01HXE7Q8N3K9Y2Z1P5V7M4W6J8',
        ];

        $validator = $this->validate($data);

        $this->assertFalse($validator->passes());
        $this->assertTrue($validator->errors()->has('license_plate'));
    }

    public function testVehicleTypeValidValues(): void
    {
        $validTypes = ['car', 'motorcycle'];

        foreach ($validTypes as $type) {
            $data = [
                'license_plate' => 'ABC-1234',
                'brand' => 'Toyota',
                'model' => 'Corolla',
                'year' => 2023,
                'client_id' => '01HXE7Q8N3K9Y2Z1P5V7M4W6J8',
                'vehicle_type' => $type,
            ];

            $validator = $this->validate($data);

            $this->assertTrue($validator->passes(), "Vehicle type '{$type}' should be valid");
        }
    }

    public function testVehicleTypeInvalidValue(): void
    {
        $data = [
            'license_plate' => 'ABC-1234',
            'brand' => 'Toyota',
            'model' => 'Corolla',
            'year' => 2023,
            'client_id' => '01HXE7Q8N3K9Y2Z1P5V7M4W6J8',
            'vehicle_type' => 'truck',
        ];

        $validator = $this->validate($data);

        $this->assertFalse($validator->passes());
        $this->assertTrue($validator->errors()->has('vehicle_type'));
    }

    public function testFuelValidValues(): void
    {
        $validFuels = ['alcohol', 'gasoline', 'diesel'];

        foreach ($validFuels as $fuel) {
            $data = [
                'license_plate' => 'ABC-1234',
                'brand' => 'Toyota',
                'model' => 'Corolla',
                'year' => 2023,
                'client_id' => '01HXE7Q8N3K9Y2Z1P5V7M4W6J8',
                'fuel' => $fuel,
            ];

            $validator = $this->validate($data);

            $this->assertTrue($validator->passes(), "Fuel '{$fuel}' should be valid");
        }
    }

    public function testFuelInvalidValue(): void
    {
        $data = [
            'license_plate' => 'ABC-1234',
            'brand' => 'Toyota',
            'model' => 'Corolla',
            'year' => 2023,
            'client_id' => '01HXE7Q8N3K9Y2Z1P5V7M4W6J8',
            'fuel' => 'electric',
        ];

        $validator = $this->validate($data);

        $this->assertFalse($validator->passes());
        $this->assertTrue($validator->errors()->has('fuel'));
    }

    public function testTransmissionValidValues(): void
    {
        $validTransmissions = ['manual', 'automatic'];

        foreach ($validTransmissions as $transmission) {
            $data = [
                'license_plate' => 'ABC-1234',
                'brand' => 'Toyota',
                'model' => 'Corolla',
                'year' => 2023,
                'client_id' => '01HXE7Q8N3K9Y2Z1P5V7M4W6J8',
                'transmission' => $transmission,
            ];

            $validator = $this->validate($data);

            $this->assertTrue($validator->passes(), "Transmission '{$transmission}' should be valid");
        }
    }

    public function testTransmissionInvalidValue(): void
    {
        $data = [
            'license_plate' => 'ABC-1234',
            'brand' => 'Toyota',
            'model' => 'Corolla',
            'year' => 2023,
            'client_id' => '01HXE7Q8N3K9Y2Z1P5V7M4W6J8',
            'transmission' => 'cvt',
        ];

        $validator = $this->validate($data);

        $this->assertFalse($validator->passes());
        $this->assertTrue($validator->errors()->has('transmission'));
    }

    public function testMileageMustBeInteger(): void
    {
        $data = [
            'license_plate' => 'ABC-1234',
            'brand' => 'Toyota',
            'model' => 'Corolla',
            'year' => 2023,
            'client_id' => '01HXE7Q8N3K9Y2Z1P5V7M4W6J8',
            'mileage' => 'not-a-number',
        ];

        $validator = $this->validate($data);

        $this->assertFalse($validator->passes());
        $this->assertTrue($validator->errors()->has('mileage'));
    }

    public function testMileageCannotBeNegative(): void
    {
        $data = [
            'license_plate' => 'ABC-1234',
            'brand' => 'Toyota',
            'model' => 'Corolla',
            'year' => 2023,
            'client_id' => '01HXE7Q8N3K9Y2Z1P5V7M4W6J8',
            'mileage' => -100,
        ];

        $validator = $this->validate($data);

        $this->assertFalse($validator->passes());
        $this->assertTrue($validator->errors()->has('mileage'));
    }

    public function testVinMaxLength(): void
    {
        $data = [
            'license_plate' => 'ABC-1234',
            'brand' => 'Toyota',
            'model' => 'Corolla',
            'year' => 2023,
            'client_id' => '01HXE7Q8N3K9Y2Z1P5V7M4W6J8',
            'vin' => str_repeat('A', 18),
        ];

        $validator = $this->validate($data);

        $this->assertFalse($validator->passes());
        $this->assertTrue($validator->errors()->has('vin'));
    }

    public function testColorMaxLength(): void
    {
        $data = [
            'license_plate' => 'ABC-1234',
            'brand' => 'Toyota',
            'model' => 'Corolla',
            'year' => 2023,
            'client_id' => '01HXE7Q8N3K9Y2Z1P5V7M4W6J8',
            'color' => str_repeat('A', 51),
        ];

        $validator = $this->validate($data);

        $this->assertFalse($validator->passes());
        $this->assertTrue($validator->errors()->has('color'));
    }

    public function testCilinderCapacityMaxLength(): void
    {
        $data = [
            'license_plate' => 'ABC-1234',
            'brand' => 'Honda',
            'model' => 'CB500',
            'year' => 2023,
            'client_id' => '01HXE7Q8N3K9Y2Z1P5V7M4W6J8',
            'cilinder_capacity' => str_repeat('A', 51),
        ];

        $validator = $this->validate($data);

        $this->assertFalse($validator->passes());
        $this->assertTrue($validator->errors()->has('cilinder_capacity'));
    }

    public function testObservationsMaxLength(): void
    {
        $data = [
            'license_plate' => 'ABC-1234',
            'brand' => 'Toyota',
            'model' => 'Corolla',
            'year' => 2023,
            'client_id' => '01HXE7Q8N3K9Y2Z1P5V7M4W6J8',
            'observations' => str_repeat('A', 1001),
        ];

        $validator = $this->validate($data);

        $this->assertFalse($validator->passes());
        $this->assertTrue($validator->errors()->has('observations'));
    }

    public function testOptionalFieldsCanBeNull(): void
    {
        $data = [
            'license_plate' => 'ABC-1234',
            'brand' => 'Toyota',
            'model' => 'Corolla',
            'year' => 2023,
            'client_id' => '01HXE7Q8N3K9Y2Z1P5V7M4W6J8',
            'color' => null,
            'vin' => null,
            'fuel' => null,
            'transmission' => null,
            'mileage' => null,
            'cilinder_capacity' => null,
            'vehicle_type' => null,
            'observations' => null,
        ];

        $validator = $this->validate($data);

        $this->assertTrue($validator->passes());
    }

    public function testAllValidFieldsTogether(): void
    {
        $data = [
            'license_plate' => 'ABC-1234',
            'brand' => 'Toyota',
            'model' => 'Corolla',
            'year' => 2023,
            'client_id' => '01HXE7Q8N3K9Y2Z1P5V7M4W6J8',
            'color' => 'Branco',
            'vin' => '1HGBH41JXMN109186',
            'fuel' => 'gasoline',
            'transmission' => 'automatic',
            'mileage' => 50000,
            'cilinder_capacity' => '2.0L',
            'vehicle_type' => 'car',
            'observations' => 'Veículo em excelente estado',
        ];

        $validator = $this->validate($data);

        $this->assertTrue($validator->passes());
    }
}
