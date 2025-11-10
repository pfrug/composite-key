# Laravel Composite Key Support

`pfrug/composite-key` provides support for composite primary keys in Eloquent models.

Laravel does not natively support composite primary keys, as mentioned in the official documentation:  
https://laravel.com/docs/12.x/eloquent#composite-primary-keys

This package offers a lightweight and flexible solution to enable composite key support for:

- `find()` and `findOrFail()` methods  
- `save()` and `delete()` operations  
- Basic Eloquent relationships:  
  - `hasManyComposite()`  
  - `belongsToComposite()`

## Installation

Install via Composer:

```bash
composer require pfrug/composite-key
```

## Usage

### Model Setup

```php
use Pfrug\CompositeKey\Traits\HasCompositeKey;
use Illuminate\Database\Eloquent\Model;

class ShipmentHeader extends Model
{
    use HasCompositeKey;

    protected $compositeKey = ['company_code', 'shipment_number'];
}
```

### Finding Records

```php
ShipmentHeader::find(['COMP001', 'SHIP123']);
ShipmentHeader::findOrFail(['COMP001', 'SHIP123']);
```

## Relationships

### hasManyComposite

```php
$this->hasManyComposite(
    RelatedModel::class,
    ['foreign_key_1', 'foreign_key_2'],
    ['local_key_1', 'local_key_2']
);
```

### belongsToComposite

```php
$this->belongsToComposite(
    RelatedModel::class,
    ['foreign_key_1', 'foreign_key_2'],
    ['owner_key_1', 'owner_key_2']
);
```

## Route Model Binding

### HasCompositeRouteKey

For models that need to be resolved from route parameters, use the `HasCompositeRouteKey` trait:

```php
use Pfrug\CompositeKey\Traits\HasCompositeKey;
use Pfrug\CompositeKey\Traits\HasCompositeRouteKey;
use Illuminate\Database\Eloquent\Model;

class ShipmentHeader extends Model
{
    use HasCompositeKey, HasCompositeRouteKey;

    protected $compositeKey = ['company_code', 'shipment_number'];
}
```

This trait overrides `getRouteKey()` and `resolveRouteBinding()` for composite key support in route parameters.

It enables route model binding for composite keys by:

- **Encoding composite keys**: Converts composite key values to URL-safe strings using `:` as separator
- **Decoding route parameters**: Resolves models from encoded composite key strings in URLs

### Usage in Routes

```php
// Route definition
Route::get('/shipments/{shipment}', function (ShipmentHeader $shipment) {
    return $shipment;
});

// URL: /shipments/COMP001:SHIP123
// Resolves to: ShipmentHeader::find(['COMP001', 'SHIP123'])
```

The route key format uses `:` as separator between composite key parts:
- `company_code:shipment_number`
- Example: `COMP001:SHIP123`

**Note**: When generating URLs with Laravel's `route()` helper, use the model instance to get the properly formatted composite key:

```php
$shipment = ShipmentHeader::find(['COMP001', 'SHIP123']);
$url = route('shipments.show', $shipment); // Generates: /shipments/COMP001:SHIP123
```

## Example: Composite Relationships in Practice

### Student.php

```php
class Student extends Model
{
    public function enrollments()
    {
        return $this->hasMany(Enrollment::class, 'student_id');
    }
}
```

### Course.php

```php
class Course extends Model
{
    public function enrollments()
    {
        return $this->hasMany(Enrollment::class, 'course_id');
    }
}
```

### Enrollment.php

```php
use Pfrug\CompositeKey\Models\CompositeModel;

class Enrollment extends CompositeModel
{
    protected $compositeKey = ['student_id', 'course_id'];

    public function student()
    {
        return $this->belongsTo(Student::class, 'student_id');
    }

    public function course()
    {
        return $this->belongsTo(Course::class, 'course_id');
    }

    public function grades()
    {
        return $this->hasManyComposite(
            Grade::class,
            ['student_id', 'course_id'],
            ['student_id', 'course_id']
        );
    }
}
```

### Grade.php

```php
use Pfrug\CompositeKey\Models\CompositeModel;

class Grade extends CompositeModel
{
    protected $compositeKey = ['student_id', 'course_id'];

    public function enrollment()
    {
        return $this->belongsToComposite(
            Enrollment::class,
            ['student_id', 'course_id'],
            ['student_id', 'course_id']
        );
    }
}
```

### Sample Usage

```php
// Retrieve record with composite key
$enrollment = Enrollment::find([1, 1]);

// Update record
$enrollment->grade = 'B+';
$enrollment->save();

// Access belongsTo relation
echo $enrollment->student->name;

// Access hasManyComposite relation
foreach ($enrollment->grades as $grade) {
    echo $grade->value;
}

// Access belongsToComposite relation from Grade
$grade = Grade::first();
echo $grade->enrollment->course_id;
```


## Eager Loading with Composite Keys

Composite relationships defined with `hasManyComposite` and `belongsToComposite` fully support eager loading via `with()` or `load()`.

### Example

```php
use App\Models\Course;
use App\Models\Grade;

// 1) Eager-load enrollments and grades when retrieving courses
$courses = Course::with(['enrollments.grades'])->get();

foreach ($courses as $course) {
    foreach ($course->enrollments as $enrollment) {
        foreach ($enrollment->grades as $grade) {
            echo $grade->value;
        }
    }
}

// 2) Eager-load course and student when retrieving grades
$grades = Grade::with(['enrollment.course', 'enrollment.student'])->get();

foreach ($grades as $grade) {
    echo $grade->enrollment->course->name;
    echo $grade->enrollment->student->name;
}
```

## Compatibility

- Laravel 10+
- PHP 8.2+

## License

This package is open-sourced software licensed under the [MIT license](LICENSE).
