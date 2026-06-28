'use client'

import * as React from 'react'
import {
  makeStyles,
  tokens,
  typographyStyles,
  Title1,
  Title3,
  Subtitle2,
  Body1,
  Caption1,
  Button,
  ToggleButton,
  CompoundButton,
  Input,
  Label,
  Field,
  Dropdown,
  Option,
  Checkbox,
  Switch,
  Slider,
  Card,
  CardHeader,
  CardFooter,
  CardPreview,
  Avatar,
  Badge,
  CounterBadge,
  Divider,
  Link,
  ProgressBar,
  TabList,
  Tab,
  Persona,
  Tag,
  TagGroup,
  MessageBar,
  MessageBarBody,
  MessageBarTitle,
  Tooltip,
} from '@fluentui/react-components'
import {
  WeatherSunny24Regular,
  WeatherMoon24Regular,
  Send24Regular,
  CalendarLtr24Regular,
  PeopleTeam24Regular,
  DataTrending24Regular,
  Add16Regular,
} from '@fluentui/react-icons'
import { useThemeMode } from '@/app/providers'

const useStyles = makeStyles({
  page: {
    minHeight: '100vh',
    backgroundColor: tokens.colorNeutralBackground2,
    color: tokens.colorNeutralForeground1,
  },
  header: {
    display: 'flex',
    alignItems: 'center',
    justifyContent: 'space-between',
    flexWrap: 'wrap',
    gap: tokens.spacingHorizontalM,
    paddingLeft: tokens.spacingHorizontalXXL,
    paddingRight: tokens.spacingHorizontalXXL,
    paddingTop: tokens.spacingVerticalM,
    paddingBottom: tokens.spacingVerticalM,
    backgroundColor: tokens.colorNeutralBackground1,
    borderBottom: `${tokens.strokeWidthThin} solid ${tokens.colorNeutralStroke2}`,
    position: 'sticky',
    top: 0,
    zIndex: 10,
  },
  logo: {
    display: 'flex',
    alignItems: 'center',
    gap: tokens.spacingHorizontalS,
    height: '28px',
  },
  main: {
    maxWidth: '1100px',
    marginLeft: 'auto',
    marginRight: 'auto',
    paddingLeft: tokens.spacingHorizontalXXL,
    paddingRight: tokens.spacingHorizontalXXL,
    paddingTop: tokens.spacingVerticalXXL,
    paddingBottom: tokens.spacingVerticalXXXL,
    display: 'flex',
    flexDirection: 'column',
    gap: tokens.spacingVerticalXXL,
  },
  hero: {
    display: 'flex',
    flexDirection: 'column',
    gap: tokens.spacingVerticalS,
    maxWidth: '640px',
  },
  heroActions: {
    display: 'flex',
    gap: tokens.spacingHorizontalM,
    marginTop: tokens.spacingVerticalM,
    flexWrap: 'wrap',
  },
  section: {
    display: 'flex',
    flexDirection: 'column',
    gap: tokens.spacingVerticalM,
  },
  grid: {
    display: 'grid',
    gridTemplateColumns: 'repeat(auto-fit, minmax(280px, 1fr))',
    gap: tokens.spacingHorizontalL,
  },
  swatchRow: {
    display: 'grid',
    gridTemplateColumns: 'repeat(auto-fit, minmax(120px, 1fr))',
    gap: tokens.spacingHorizontalM,
  },
  swatch: {
    display: 'flex',
    flexDirection: 'column',
    gap: tokens.spacingVerticalXS,
  },
  swatchColor: {
    height: '56px',
    borderRadius: tokens.borderRadiusMedium,
    border: `${tokens.strokeWidthThin} solid ${tokens.colorNeutralStroke2}`,
  },
  swatchLabel: {
    color: tokens.colorNeutralForeground3,
    overflowWrap: 'anywhere',
  },
  typeScale: {
    display: 'flex',
    flexDirection: 'column',
    gap: tokens.spacingVerticalS,
  },
  typeRow: {
    display: 'flex',
    alignItems: 'baseline',
    gap: tokens.spacingHorizontalL,
    flexWrap: 'wrap',
  },
  typeLabel: {
    ...typographyStyles.caption1,
    color: tokens.colorNeutralForeground3,
    minWidth: '88px',
  },
  cardBody: {
    paddingLeft: tokens.spacingHorizontalM,
    paddingRight: tokens.spacingHorizontalM,
    display: 'flex',
    flexDirection: 'column',
    gap: tokens.spacingVerticalM,
  },
  controlsRow: {
    display: 'flex',
    flexWrap: 'wrap',
    gap: tokens.spacingHorizontalM,
    alignItems: 'center',
  },
  statValue: {
    ...typographyStyles.title2,
    color: tokens.colorBrandForeground1,
  },
  previewBanner: {
    display: 'flex',
    alignItems: 'center',
    justifyContent: 'center',
    height: '96px',
    backgroundColor: tokens.colorBrandBackground2,
    color: tokens.colorBrandForeground2,
  },
})

const brandSwatches = [
  { name: 'colorBrandBackground', value: tokens.colorBrandBackground },
  {
    name: 'colorBrandBackgroundHover',
    value: tokens.colorBrandBackgroundHover,
  },
  { name: 'colorBrandBackground2', value: tokens.colorBrandBackground2 },
  { name: 'colorNeutralBackground1', value: tokens.colorNeutralBackground1 },
  { name: 'colorNeutralBackground3', value: tokens.colorNeutralBackground3 },
  {
    name: 'colorPaletteGreenBackground3',
    value: tokens.colorPaletteGreenBackground3,
  },
  {
    name: 'colorPaletteRedBackground3',
    value: tokens.colorPaletteRedBackground3,
  },
  {
    name: 'colorPaletteYellowBackground3',
    value: tokens.colorPaletteYellowBackground3,
  },
]

export function StarterPage() {
  const styles = useStyles()
  const { mode, setMode } = useThemeMode()
  const [progress] = React.useState(0.72)

  return (
    <div className={styles.page}>
      <header className={styles.header}>
        <div className={styles.logo}>
          {/* Fluent UI logo from the design system's own docsite assets */}
          <img
            src="/fluentui-logo.svg"
            alt="Fluent UI"
            height={24}
            style={mode === 'dark' ? { filter: 'invert(1)' } : undefined}
          />
        </div>
        <div className={styles.controlsRow}>
          <TabList defaultSelectedValue="overview" size="small">
            <Tab value="overview">Overview</Tab>
            <Tab value="components">Components</Tab>
            <Tab value="tokens">Tokens</Tab>
          </TabList>
          <Tooltip
            content={
              mode === 'light'
                ? 'Switch to dark theme'
                : 'Switch to light theme'
            }
            relationship="label"
          >
            <ToggleButton
              checked={mode === 'dark'}
              onClick={() => setMode(mode === 'light' ? 'dark' : 'light')}
              icon={
                mode === 'light' ? (
                  <WeatherMoon24Regular />
                ) : (
                  <WeatherSunny24Regular />
                )
              }
              appearance="subtle"
              aria-label="Toggle dark theme"
            />
          </Tooltip>
        </div>
      </header>

      <main className={styles.main}>
        <section className={styles.hero}>
          <Badge appearance="tint" color="brand">
            Fluent 2 Design System
          </Badge>
          <Title1 as="h1">Build with Fluent UI React</Title1>
          <Body1>
            Microsoft&apos;s open-source design system for building coherent,
            accessible, high-quality web experiences. Every color, type style,
            and spacing value on this page comes from Fluent design tokens.
          </Body1>
          <div className={styles.heroActions}>
            <Button appearance="primary" icon={<Send24Regular />}>
              Get started
            </Button>
            <Button appearance="secondary">Browse components</Button>
            <Link href="https://fluent2.microsoft.design" target="_blank">
              Fluent 2 guidelines
            </Link>
          </div>
        </section>

        <Divider />

        <section className={styles.section} aria-labelledby="tokens-heading">
          <Title3 as="h2" id="tokens-heading">
            Color tokens
          </Title3>
          <div className={styles.swatchRow}>
            {brandSwatches.map((swatch) => (
              <div key={swatch.name} className={styles.swatch}>
                <div
                  className={styles.swatchColor}
                  style={{ backgroundColor: swatch.value }}
                />
                <Caption1 className={styles.swatchLabel}>
                  {swatch.name}
                </Caption1>
              </div>
            ))}
          </div>
        </section>

        <section className={styles.section} aria-labelledby="type-heading">
          <Title3 as="h2" id="type-heading">
            Type ramp
          </Title3>
          <div className={styles.typeScale}>
            <div className={styles.typeRow}>
              <span className={styles.typeLabel}>Title1</span>
              <Title1>The quick brown fox</Title1>
            </div>
            <div className={styles.typeRow}>
              <span className={styles.typeLabel}>Title3</span>
              <Title3>The quick brown fox</Title3>
            </div>
            <div className={styles.typeRow}>
              <span className={styles.typeLabel}>Subtitle2</span>
              <Subtitle2>The quick brown fox</Subtitle2>
            </div>
            <div className={styles.typeRow}>
              <span className={styles.typeLabel}>Body1</span>
              <Body1>The quick brown fox jumps over the lazy dog</Body1>
            </div>
            <div className={styles.typeRow}>
              <span className={styles.typeLabel}>Caption1</span>
              <Caption1>The quick brown fox jumps over the lazy dog</Caption1>
            </div>
          </div>
        </section>

        <section
          className={styles.section}
          aria-labelledby="compositions-heading"
        >
          <Title3 as="h2" id="compositions-heading">
            Components in context
          </Title3>
          <div className={styles.grid}>
            <Card>
              <CardHeader
                header={<Subtitle2>Project settings</Subtitle2>}
                description={<Caption1>Configure your workspace</Caption1>}
              />
              <div className={styles.cardBody}>
                <Field label="Project name" required>
                  <Input placeholder="My Fluent app" />
                </Field>
                <Field label="Region">
                  <Dropdown
                    placeholder="Select a region"
                    defaultValue="West Europe"
                  >
                    <Option>East US</Option>
                    <Option>West Europe</Option>
                    <Option>Southeast Asia</Option>
                  </Dropdown>
                </Field>
                <Checkbox label="Enable usage analytics" defaultChecked />
                <Switch label="Automatic deployments" defaultChecked />
              </div>
              <CardFooter>
                <Button appearance="primary">Save changes</Button>
                <Button appearance="secondary">Cancel</Button>
              </CardFooter>
            </Card>

            <Card>
              <CardPreview>
                <div className={styles.previewBanner}>
                  <DataTrending24Regular fontSize={48} />
                </div>
              </CardPreview>
              <CardHeader
                header={<Subtitle2>Quarterly usage</Subtitle2>}
                description={<Caption1>Updated 5 minutes ago</Caption1>}
              />
              <div className={styles.cardBody}>
                <span className={styles.statValue}>12,840</span>
                <Body1>Active sessions this quarter</Body1>
                <Field label="Capacity used" hint="72% of plan limit">
                  <ProgressBar value={progress} thickness="large" />
                </Field>
                <TagGroup aria-label="Usage tags">
                  <Tag size="small">Production</Tag>
                  <Tag size="small">EU region</Tag>
                  <Tag size="small">Auto-scale</Tag>
                </TagGroup>
              </div>
            </Card>

            <Card>
              <CardHeader
                image={
                  <Avatar
                    color="brand"
                    icon={<PeopleTeam24Regular />}
                    aria-hidden
                  />
                }
                header={<Subtitle2>Team activity</Subtitle2>}
                description={<Caption1>3 members online</Caption1>}
                action={<CounterBadge count={5} color="danger" />}
              />
              <div className={styles.cardBody}>
                <Persona
                  name="Ada Lovelace"
                  secondaryText="Updated the design tokens"
                  presence={{ status: 'available' }}
                />
                <Persona
                  name="Grace Hopper"
                  secondaryText="Reviewed 4 pull requests"
                  presence={{ status: 'busy' }}
                />
                <Persona
                  name="Alan Turing"
                  secondaryText="Commented on Navigation spec"
                  presence={{ status: 'away' }}
                />
                <MessageBar intent="success">
                  <MessageBarBody>
                    <MessageBarTitle>All checks passed.</MessageBarTitle>
                    The latest build is ready to ship.
                  </MessageBarBody>
                </MessageBar>
              </div>
              <CardFooter>
                <Button appearance="outline" icon={<Add16Regular />}>
                  Invite member
                </Button>
              </CardFooter>
            </Card>
          </div>
        </section>

        <section className={styles.section} aria-labelledby="controls-heading">
          <Title3 as="h2" id="controls-heading">
            Buttons and inputs
          </Title3>
          <div className={styles.controlsRow}>
            <Button appearance="primary">Primary</Button>
            <Button appearance="secondary">Secondary</Button>
            <Button appearance="outline">Outline</Button>
            <Button appearance="subtle">Subtle</Button>
            <Button appearance="transparent">Transparent</Button>
            <Button appearance="primary" disabled>
              Disabled
            </Button>
          </div>
          <div className={styles.controlsRow}>
            <CompoundButton
              icon={<CalendarLtr24Regular />}
              secondaryContent="Schedule a meeting"
            >
              New event
            </CompoundButton>
            <div style={{ minWidth: '220px', flexGrow: 1, maxWidth: '320px' }}>
              <Label htmlFor="volume-slider">Volume</Label>
              <Slider id="volume-slider" defaultValue={60} />
            </div>
          </div>
        </section>
      </main>
    </div>
  )
}
