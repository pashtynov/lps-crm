<aside> 
				<div class="aside-wrap">
					<a class="aside-wrap__logo" href="{{ route('objects.index') }}"><img class="d-none d-md-block" src="{{ asset('verstka/img/svg/logo.svg') }}" alt="" loading="lazy"/><img class="d-md-none" src="{{ asset('verstka/img/svg/logo-sm.svg') }}" alt="" loading="lazy"/>
					</a>
					<div class="aside-wrap__nav">
						<ul>
							<li><a class="active" href="{{ route('objects.index') }}" data-bs-toggle="tooltip" data-bs-placement="right" title="Объекты">
									<svg class="icon icon-objects ">
										<use xlink:href="{{ asset('verstka/img/svg/sprite.svg') }}#objects"></use>
									</svg><span>Объекты</span></a></li>
							<li><a href="{{ route('concept', 'today') }}" data-bs-toggle="tooltip" data-bs-placement="right" title="Задачи (концепт)">
									<svg class="icon icon-tasks ">
										<use xlink:href="{{ asset('verstka/img/svg/sprite.svg') }}#tasks"></use>
									</svg><span>Задачи</span></a></li>
							<li><a href="{{ route('concept', 'brigades') }}" data-bs-toggle="tooltip" data-bs-placement="right" title="Бригады (концепт)">
									<svg class="icon icon-workers ">
										<use xlink:href="{{ asset('verstka/img/svg/sprite.svg') }}#workers"></use>
									</svg><span>Бригады</span></a></li>
							<li><a href="#" data-bs-toggle="tooltip" data-bs-placement="right" title="Документы">
									<svg class="icon icon-file ">
										<use xlink:href="{{ asset('verstka/img/svg/sprite.svg') }}#file"></use>
									</svg><span>Документы</span></a></li>
						</ul>
					</div>
					<div class="avatar-wrap">
						<form method="post" action="{{ route('logout') }}" onsubmit="return confirm('Выйти из CRM?')">
							@csrf
							<button class="avatar crm-avatar-btn" title="{{ auth()->user()?->name }} — выйти"><span>{{ $initials ?? '' }}</span></button>
						</form>
					</div>
					<a class="aside-wrap__settings" href="{{ route('catalog') }}" title="Справочник норм и цен">
						<svg class="icon icon-settings ">
							<use xlink:href="{{ asset('verstka/img/svg/sprite.svg') }}#settings"></use>
						</svg>
					</a>
				</div>
			</aside>
