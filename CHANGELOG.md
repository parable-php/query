# Parable PHP Query

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
