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

    protected array $compositeKey = ['company_code', 'shipment_number'];
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

## Compatibility

- Laravel 10+
- PHP 8.2+

## License

MIT
