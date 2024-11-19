<?php

namespace common\modules\user\enums;

enum PasswordRestoreType: int
{
    case Directly = 0;
    case ViaToken = 1;
}
