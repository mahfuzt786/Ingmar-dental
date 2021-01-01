# Subsidy Implementation
This package implements the subsidy logic of the system. The subsidy logic
is just to get the therapies (which we call in our models TherapyDecisionTree)
ids for each region (group of teeth) or even indiviaul tooth in the mouth.

It just use a teeth schema (that is, all the teeth in the mouth with its
findings), check for several different kind of rules and returns the subsidies
depending on the kind of rules that were identified.

## Definitions

To understand this implementation, you will need to comprehend the following concepts:

1. **ISO Notation**: we are using the [ISO notation](https://en.wikipedia.org/wiki/Dental_notation) to represent the Teeth Schema.

2. **Teeth Schema**: consists of 4 quandrants which are counted clockwise with
    8 teeth per quadrant (check ISO Notation).

>            upper right - 1             upper left - 2
>        18 17 16 15 14 13 12 11 | 21 22 23 24 25 26 27 28
>     R --------------------------------------------------- L
>        48 47 46 45 44 43 42 41 | 31 32 33 34 35 36 37 38
>            lower right - 4             lower left - 3

3. **TBR "to be replaced"** tooth is a tooth with the following status/findings:
    a) `f`
    b) `x`
    c) `b` + neighbouring finding `ww/kw/tw/pw/rw`
    d) `ew`
    e) `sw`
    `)(` doesnt count as a TBR, see Test Case 2 in subsidy logic (region 36)"
4. **TBT "to be treated"** tooth is a tooth with the following status/findings:
    `ww/kw/tw/pw/rw`.
5. **Interdental Gap**: it is a **TBR** between two existing **TBT** teeth. It means that we have a missing tooth between other two that need some treatment. In an interdental gap, the first tooth clockwise is called `A` tooth and the last one clockwise is called  `B` tooth.

6. **Abutment Tooth**: the `A` and `B` teeth in an **Interdental Gap** are called individually **Abutment Tooth**.

7. **Quadrant (1, 2, 3, 4)**: there are 4 quadrants with 8 teeth each, counted from 1-8 in each quadrant and beginning in the "middle" of the mouth. (see **Teeth Schema**).

8. **Upper jaw**: the upper part of the mouth, composed by quadrants 1 and 2.

9. **Mandible**: the bottom part of the mouth, composed by quadrants 3 and 4.

10. **Posterior region**: region composed by the back teeth in the mouth (more technically teeth from X4-X8 [X = Quadrant] [4-8 = tooth region] in each quadrant of the mouth.

>            upper right - 1             upper left - 2
>        18 17 16 15 14 13 12 11 | 21 22 23 24 25 26 27 28
>        ^  ^  ^  ^  ^                       ^  ^  ^  ^  ^
>     R --------------------------------------------------- L
>        48 47 46 45 44 43 42 41 | 31 32 33 34 35 36 37 38
>        ^  ^  ^  ^  ^                       ^  ^  ^  ^  ^
>            lower right - 4             lower left - 3

11. **Anterior (front) region**: teeth in the front region of the mouth (more technically: X3-X3).

>            upper right - 1             upper left - 2
>        18 17 16 15 14 13 12 11 | 21 22 23 24 25 26 27 28
>                        ^  ^  ^   ^  ^  ^
>     R --------------------------------------------------- L
>        48 47 46 45 44 43 42 41 | 31 32 33 34 35 36 37 38
>                        ^  ^  ^   ^  ^  ^
>            lower right - 4             lower left - 3


12. **Main gap**: first occuring **Interdental Gap**, clockwise view per jaw.

13. **Neighbouring Gap**: gap next to another gap divided by 1 **TBT** (clockwise view).

14. **Collateral Interdental Gap**: an **Interdental Gap** in the oposed Quadrant in the same jaw.

14. **Free End**: a Free End occurs when there are several missing teeth in the _end_ of the mouth, that is, when teeth in the **Posterior Region** are missing.

15. **Unilateral Free End**:
    a) For **Upper Jaw**:
        Quadrant 1: at least **TBR**: 18-16 `OR` Quadrant 2: **TBR**: 26-28
    b) For **Mandible**:
        Quadrant 3: **TBR**: 38-36 `OR` Quadrant 4 **TBR**: 46-48

16. **Bilateral Free End**:
    a) For **Upper Jaw**:
        Quadrant 1: at least TBR 18-16 `AND` Quadrant 2: TBR 26-28
    b) For **Mandible**:
        Quadrant 3: TBR 38-36 `AND` Quadrant 4 TBR 46-48

17. **RV values meaning**
    a) E: A tooth which needs to be replaced (e.g. for 4.x you get a prothese so all existing teeth are removed)
    b) K: A crown (a treatment for an existing teeth)
    c) T: A telescope crown (works as support)
    d) H: an retention element planned by dentists to keep it "safe" positioned, Hs belong to existing teeth - could be any tooth existing
    e) B: Part of a bridge (abutment teeth will become K, even if they have been OK before)
    f) V: A blending (added to other RV codes)

## Logics

The logics uses `regions` of teeth, its `status` and `findings` to get `subsidies` that will be returned.

Two different kind of rules are applied:

1. **By Region**: regions (several teeth) are analyzed and it return subsidies based on the findings/status of the region. This kind of rules can be found in: `rules/by_region.php` file.
2. **By Tooth**: tooth is individually analyzed and it return subsidies based on the findings/status in the tooth. This kind of rules can be found in: `rules/by_tooth.php` file.

The logics are defined in the `rules` subpackage and it is well documented by this [spreadsheet](https://docs.google.com/spreadsheets/d/1d0TTtognbnqLW4FizV8ytLblLJZSyzzvjX21Xunvads/edit?ts=5fe030ea#gid=1505158426), but you might take a look in the code because the documentation there is very clear and the code itself is easily understandable.
