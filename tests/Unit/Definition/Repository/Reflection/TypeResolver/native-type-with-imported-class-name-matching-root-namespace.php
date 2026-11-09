<?php

namespace C\D;

use C\D\C;

interface FirstInterface {}

interface SecondInterface {}

final class ClassWithIntersection
{
    public FirstInterface&SecondInterface $value;
}
