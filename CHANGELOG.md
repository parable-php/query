# Parable PHP Query

## 0.2.1

_Changes_

- `hasValueSets()` was removed but proves useful enough to put back in.
- `Order` renamed to `OrderBy`, can no longer be created without keys being passed.

## 0.2.0

_Changes_

- `Query::orderBy()` now takes an `Order` object, rather than string values. Example: `->orderBy(Order::asc('id', 'username'))`, which will parse to `ORDER BY id ASC, username ASC`. Use `::desc` for descending. You can call it multiple times, so you can do one key `ASC`, one key `DESC`, and then another `ASC` if you want.

  **NOTE**: Adding a key more than once is possible, but _not_ if they are of different directions.
- Most places where single-type values were being passed into a method as an array, it now expects the values to be passed as multiple parameters. These methods expect multiple parameters rather than an array:
  - `Order::asc(...$keys)`
  - `Order::desc(...$keys)`
  - `Query::setColumns(...$columns)`
  - `Query::groupBy(...$keys)`
- Translators are now type-hinted as `TranslatorInterface` and all methods on `AbstractTranslator` have been made protected. This makes it easier to see what functionality is necessary to expose to the outside.
- `Query` has gained some quality of life methods: `hasWhereConditions(): bool`, `hasGroupBy(): bool`, `hasOrderBy(): bool` and `countValueSets(): int`.
- `Query::createCleanClone()` has been added, which will return a copy of the query you called it on, with no further configuration whatsoever being retained. 

## 0.1.4

_Bugfixes_
- Make sure we never try to `quote()` a non-string value by string-casting before we hand it over to `PDO`.

## 0.1.3

_Changes_

- Code style fixes.
- Fixed typo in exception.

## 0.1.2

_Changes_

- Added `hasValueSets()` to `Query`
- Added `hasValues()` to `ValueSet`
- `SupportsValuesTrait` now treats `null` values to mean the value should be `NULL`-ed. Leaving out the value in the `ValueSet` values will simply not add it to the values list.

## 0.1.1

_Changes_
- Added `whereCondition` to `Query` and `onCondition` to `Join`.

_Bugfixes_
- Renamed `whereCallable` in `Join` to `onCallable`, as it should've been named in the first place.

## 0.1.0

_Changes_
- First release.
